<?php
declare(strict_types=1);

namespace Hal\Metric\Class_\Coupling;

use Hal\Metric\Helper\MetricNameGenerator;
use Hal\Metric\Helper\NodeIteratorInterface;
use Hal\Metric\Metric;
use Hal\Metric\Metrics;
use PhpParser\Node;
use PhpParser\Node\Stmt;
use PhpParser\Node\UseItem;
use PhpParser\NodeVisitorAbstract;
use Stringable;
use function array_map;
use function in_array;
use function ltrim;
use function preg_match_all;
use function property_exists;
use function str_contains;
use function strstr;
use function strtolower;

/**
 * List externals dependencies
 */
final class ExternalsVisitor extends NodeVisitorAbstract
{
    /** @var array<UseItem> */
    private array $uses = [];
    /** @var array<string> */
    private array $dependencies = [];
    /** @var array<string> */
    private array $parents = [];
    /** @var array<string> */
    private array $interfaces = [];

    /**
     * @param Metrics $metrics
     * @param NodeIteratorInterface $nodeIterator
     */
    public function __construct(
        private readonly Metrics $metrics,
        private readonly NodeIteratorInterface $nodeIterator,
    ) {
    }

    /**
     * {@inheritDoc}
     */
    public function leaveNode(Node $node): null|int|Node|array // TODO PHP 8.2: only return null here.
    {
        if ($node instanceof Stmt\Namespace_) {
            $this->uses = [];
            return null;
        }

        if ($node instanceof Stmt\Use_) {
            $this->uses = [...$this->uses, ...$node->uses];
            return null;
        }

        if (
            !$node instanceof Stmt\Class_
            && !$node instanceof Stmt\Interface_
            && !$node instanceof Stmt\Trait_
            //TODO: && !$node instanceof Stmt\Enum_ ?
            //TODO: maybe simply set !$node instanceof Stmt\ClassLike ?
        ) {
            return null;
        }

        /** @var Metric $class */
        $class = $this->metrics->get(MetricNameGenerator::getClassName($node));
        $this->parents = [];
        $this->interfaces = [];
        $this->dependencies = [];

        // Extends
        // In ClassLike instances, only Interface can have several extends.
        // In ClassLike instances, only Class can have a single extends.
        if ($node instanceof Stmt\Interface_) {
            array_map($this->addParent(...), $node->extends);
        } elseif ($node instanceof Stmt\Class_ && null !== $node->extends) {
            $this->addParent($node->extends);
        }

        // Implements
        // In ClassLike instances, only Class and Enum can have several implements.
        if ($node instanceof Stmt\Class_ /* TODO: || $node instanceof Stmt\Enum_ */) {
            array_map($this->addImplementation(...), $node->implements);
        }

        // Inside methods.
        array_map($this->checkMethods(...), $node->getMethods());

        // Look for PHPDoc annotations on class level.
        $this->addDependenciesFromPhpDocAnnotations($node);

        // TODO: Add PHP Attributes
        // TODO: Add Trait usages.
        // TODO: Add Properties typehint.

        $class->set('externals', $this->dependencies);
        $class->set('parents', $this->parents);
        $class->set('implements', $this->interfaces);

        return null;
    }

    /**
     * @param string|Stringable $dependency
     * @return void
     */
    private function addDependency(string|Stringable $dependency): void
    {
        $dependency = (string)$dependency;
        if (in_array(strtolower($dependency), ['self', 'parent'], true)) {
            return;
        }
        if (null !== ($resolvedName = $this->resolveClassName($dependency))) {
            $this->dependencies[] = $resolvedName;
        }
    }

    /**
     * @param string|Stringable $parent
     * @return void
     */
    private function addParent(string|Stringable $parent): void
    {
        $parent = (string)$parent;
        $this->parents[] = ltrim($parent, '\\');
        // Parenting is a dependency.
        $this->addDependency($parent);
    }

    /**
     * @param string|Stringable $implements
     * @return void
     */
    private function addImplementation(string|Stringable $implements): void
    {
        $implements = (string)$implements;
        $this->interfaces[] = ltrim($implements, '\\');
        // Implementation is a dependency.
        $this->addDependency($implements);
    }

    /**
     * Checks the use of dependencies inside a classMethod.
     * @param Stmt\ClassMethod $stmt
     * @return void
     */
    private function checkMethods(Stmt\ClassMethod $stmt): void
    {
        // Return types in methods.
        $this->addDependenciesFromTypeHint($stmt->returnType);

        // Type hint of method's arguments
        array_map(function (Node\Param $param): void {
            $this->addDependenciesFromTypeHint($param->type);
        }, $stmt->params);

        // Direct call by class name:
        // - new MyClass();
        // - MyClass::staticCall();
        // - MyClass::$staticProperty;
        // - MyClass::class;
        // - instanceof MyClass;
        $this->nodeIterator->iterateOver($stmt, function (Node $node): void {
            if ($node instanceof Node\Expr && property_exists($node, 'class') && $node->class instanceof Node\Name) {
                $this->addDependency($node->class);
            }
        });

        // PHP Doc annotations.
        $this->addDependenciesFromPhpDocAnnotations($stmt);
    }

    /**
     * Add dependencies inferred by the node representing a type hint (from parameter or return type) given in argument.
     *
     * @param null|Node\Identifier|Node\Name|Node\ComplexType $node
     * @return void
     */
    private function addDependenciesFromTypeHint(null|Node\Identifier|Node\Name|Node\ComplexType $node): void
    {
        if ($node instanceof Node\Name) {
            $this->addDependency($node);
            return;
        }
        if ($node instanceof Node\NullableType) {
            $this->addDependency($node->type);
            return;
        }
        if ($node instanceof Node\UnionType || $node instanceof Node\IntersectionType) {
            array_map($this->addDependenciesFromTypeHint(...), $node->types);
        }
    }

    /**
     * Add dependencies inferred by looking at the PHPDoc annotations. Replace PHPDoc annotations by real namespace name
     * when an alias is used.
     *
     * @param Node $node
     * @return void
     */
    private function addDependenciesFromPhpDocAnnotations(Node $node): void
    {
        $comments = $node->getDocComment();
        /** @var scalar $reformattedTest */
        $reformattedTest = $comments?->getReformattedText();
        preg_match_all('!\s+\*\s+@([\w\\\\]+)!', (string)$reformattedTest, $matches);
        $annotations = $matches[1] ?? [];
        array_map($this->addDependency(...), $annotations);
    }

    /**
     * Resolves the class name to get the fully qualified class name even if given class name is referring to an alias.
     *
     * @param string $classNameToResolve
     * @return null|string The fully qualified class name with aliases resolved, or NULL if the given class name is not
     *     resolvable.
     */
    private function resolveClassName(string $classNameToResolve): null|string
    {
        // If class name is using root namespace, it's already resolved.
        if ('\\' === $classNameToResolve[0]) {
            return ltrim($classNameToResolve, '\\');
        }

        // Otherwise, check with the defined `use` statements.
        foreach ($this->uses as $use) {
            $useAlias = (string)$use->getAlias();
            if ($useAlias === $classNameToResolve && !str_contains($classNameToResolve, '\\')) {
                return (string)$use->name;
            }
            if ($useAlias === strstr($classNameToResolve, '\\', true)) {
                return $use->name . strstr($classNameToResolve, '\\');
            }
        }

        // If not found, the given string is not a resolvable class name.
        return null;
    }
}
