<?php
/**
 * (c) Jean-François Lépine <https://twitter.com/Halleck45>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Hal\Metric\Class_\Coupling;

use Hal\Metric\Helper\MetricClassNameGenerator;
use Hal\Metric\MetricsVisitorTrait;
use PhpParser\Node;
use PhpParser\Node\Name\FullyQualified;
use PhpParser\Node\Stmt;
use PhpParser\Node\Stmt\ClassMethod;
use PhpParser\NodeVisitorAbstract;

/**
 * Class ExternalsVisitor
 * List externals dependencies.
 *
 * @package Hal\Metric\Class_\Coupling
 */
class ExternalsVisitor extends NodeVisitorAbstract
{
    use MetricsVisitorTrait;

    /** @var Stmt\UseUse[] List of external dependencies found with the "use" keyword. */
    private $uses = [];

    /** @var string[] List of all parents for a given element to be parsed. */
    private $parents = [];

    /** @var string[] List of all external dependencies for a given element to be parsed. */
    private $externals = [];

    /**
     * Executed when leaving the traversing of the node. Used to calculates the following elements:
     * - List of dependent classes, traits or interfaces, called "externals"
     * - List of parent classes, traits or interfaces, called "parents"
     * @param Node $node The current node to leave to make the analysis.
     * @return void
     */
    public function leaveNode(Node $node)
    {
        // Reset the list of uses when finding a namespace statement. Nothing more to do.
        if ($node instanceof Stmt\Namespace_) {
            $this->uses = [];
            return;
        }

        // Set the list of uses when finding a use statement. Nothing more to do.
        if ($node instanceof Stmt\Use_) {
            $this->uses = \array_merge($this->uses, $node->uses);
        }

        // Do not find all parents and dependencies when dealing with something that is not a ClassLike statement.
        // Also, stop here if the metrics of the element has not been already created.
        $class = $this->metrics->get(MetricClassNameGenerator::getName($node));
        if (!($node instanceof Stmt\ClassLike) || null === $class) {
            return;
        }

        // Reset the parents and the externals.
        $this->parents = [];
        $this->externals = [];
        $this->parseExtends($node)
            ->parseImplements($node);
        array_map([$this, 'parseStatements', $node->stmts]);

        $class->set('externals', $this->externals);
        $class->set('parents', $this->parents);
    }

    /**
     * Parses the "extends" statement of the current node.
     *
     * @param Node $node The current node to check the "extends" statements.
     * @return $this
     */
    private function parseExtends(Node $node)
    {
        if (!isset($node->extends)) {
            return $this;
        }

        /** @var Node\Name[] $extends */
        $extends = is_array($node->extends) ? $node->extends : [$node->extends];
        foreach ($extends as $interface) {
            $stringName = $interface->toString();
            $this->externals[] = $stringName;
            $this->parents[] = $stringName;
        }

        return $this;
    }

    /**
     * Parses the "implements" statement of the current node.
     *
     * @param Node $node The current node to check the "implements" statements.
     * @return $this
     */
    private function parseImplements(Node $node)
    {
        if (!isset($node->implements)) {
            return $this;
        }

        /** @var Node\Name[] $implements */
        $implements = $node->implements;
        foreach ($implements as $interface) {
            $this->externals[] = $interface->toString();
        }

        return $this;
    }

    /**
     * Parses the given statement only if it is a ClassMethod.
     *
     * @param Stmt $stmt The current statement to parse.
     */
    private function parseStatements(Stmt $stmt)
    {
        // Ignore statements that are not methods.
        if (!($stmt instanceof ClassMethod)) {
            return;
        }

        // Parse the ReturnType.
        if (isset($stmt->returnType) && $stmt->returnType instanceof FullyQualified) {
            $this->externals[] = $stmt->returnType->toString();
        }

        // Parse the type hint of method's parameters.
        foreach ($stmt->params as $param) {
            if ($param->type && $param->type instanceof FullyQualified) {
                $this->externals[] = $param->type->toString();
            }
        }

        // Parse instantiations and static calls.
        $dependencies = [];
        \iterate_over_node($stmt, function ($node) use (&$dependencies) {
            switch (true) {
                case $node instanceof Node\Expr\New_:
                    // new MyClass
                    $dependencies[] = \getNameOfNode($node);
                    break;
                case $node instanceof Node\Expr\StaticCall:
                    // MyClass::Call
                    $dependencies[] = \getNameOfNode($node);
                    break;
            }
        });
        $this->externals[] = array_merge($this->externals, $dependencies);

        // Parse annotations.
        $comments = $stmt->getDocComment();
        if ($comments && false !== \preg_match_all('!\s+\*\s+@(\w+)!', $comments->getText(), $matches)) {
            foreach ($matches[1] as $check) {
                foreach ($this->uses as $use) {
                    if ($use->alias === $check) {
                        $this->externals[] = $use->name->toString();
                    }
                }
            }
        }
    }
}
