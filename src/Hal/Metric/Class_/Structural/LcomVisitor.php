<?php
declare(strict_types=1);

namespace Hal\Metric\Class_\Structural;

use Closure;
use Hal\Component\Tree\GraphDeduplicated;
use Hal\Component\Tree\Node as TreeNode;
use Hal\Metric\Helper\DetectorInterface;
use Hal\Metric\Helper\MetricNameGenerator;
use Hal\Metric\Helper\NodeIteratorInterface;
use Hal\Metric\Metric;
use Hal\Metric\Metrics;
use PhpParser\Modifiers;
use PhpParser\Node;
use PhpParser\Node\Stmt;
use PhpParser\NodeVisitorAbstract;
use Stringable;
use function array_filter;
use function array_map;
use function in_array;
use function is_string;
use function property_exists;

/**
 * Calculates the Lack of cohesion of methods.
 * This value is measuring the different sub-parts inside a class and returns the number of sub-graphs a class is
 * composed of. Nodes of the sub-graphs are properties and methods and edges are relations between them.
 * Magic methods and accessors are ignored as they are not breaking the cohesion of methods.
 * Calling `new self`, `new __CLASS__`, `new MyClass()` or, from a final class, `new static`, should add an edge with
 * the constructor, but as the constructor is ignored, it has to create a link with each property fetched and each
 * method called in the constructor.
 *
 * For instance, with the following class, the LCoM will be 2 as there are 2 disjointed graphs:
 * ```php
 * class Foo
 * {
 *     public function __construct(private readonly int $n, private readonly int $b) {}
 *     public function getTwiceN(): int { return 2 * $this->n; }
 *     public function getTwiceB(): int { return 2 * $this->b; }
 * }
 * ```
 * As explained, there is 2 sub-graphs here:
 * - n <=> getTwiceN
 * - b <=> getTwiceB
 *
 * Considering the addition of the following method will reduce the LCoM to 1:
 * ```php
 * class Foo
 * {
 *     public function __construct(private readonly int $n, private readonly int $b) {}
 *     public function getTwiceN(): int { return 2 * $this->n; }
 *     public function getTwiceB(): int { return 2 * $this->b; }
 *     public function getTotal(): int { return $this->getTwiceN() + $this->getTwiceB(); }
 * }
 * ```
 * There is now only 1 sub-graph representing this class:
 * - n <=> getTwiceN <=> getTotal <=> getTwiceB <=> b
 *
 * The LCoM value is also associated to the number of responsibilities a class owns.
 * To respect the "S" from SOLID principle, the LCoM value must be 0 (empty class) or 1 (single responsibility class).
 */
final class LcomVisitor extends NodeVisitorAbstract
{
    private GraphDeduplicated $graph;

    /**
     * @param Metrics $metrics
     * @param NodeIteratorInterface $nodeIterator
     * @param DetectorInterface $roleOfMethodDetector
     */
    public function __construct(
        private readonly Metrics $metrics,
        private readonly NodeIteratorInterface $nodeIterator,
        private readonly DetectorInterface $roleOfMethodDetector,
    ) {
    }

    /**
     * {@inheritDoc}
     */
    public function leaveNode(Node $node): null|int|Node|array // TODO PHP 8.2: only return null here.
    {
        if (
            !$node instanceof Stmt\Class_
            && !$node instanceof Stmt\Trait_
            // TODO: && !$node instanceof Stmt\Enum_
        ) {
            return null;
        }

        // We build a graph of internal dependencies in class.
        $this->graph = new GraphDeduplicated();
        /** @var Metric $class */
        $class = $this->metrics->get(MetricNameGenerator::getClassName($node));

        // We don't want to increase the LCOM for getters and setters.
        $methods = array_filter($node->getMethods(), function (Stmt\ClassMethod $stmt): bool {
            return !in_array($this->roleOfMethodDetector->detects($stmt), ['getter', 'setter'], true);
        });
        array_map($this->buildGraph(...), $methods);

        $nbSubGraphs = 0;
        foreach ($this->graph->all() as $traversableNode) {
            $nbSubGraphs += $this->traverse($traversableNode);
        }

        $class->set('lcom', $nbSubGraphs);

        return null;
    }

    /**
     * Builds the relation graph for the current method.
     *
     * @param Stmt\ClassMethod $stmt
     * @return void
     */
    private function buildGraph(Stmt\ClassMethod $stmt): void
    {
        $from = $this->graph->gather($stmt->name . '()');
        $this->nodeIterator->iterateOver($stmt, $this->getVisitorCallback($from));
    }

    /**
     * Returns the callback that helps to calculate the Lcom metric from the current graph.
     *
     * @param TreeNode $from Node that is the origin of all edges added to the graph held by the current visitor.
     * @return Closure
     */
    private function getVisitorCallback(TreeNode $from): Closure
    {
        return function (Node $node) use ($from): void {
            $relations = [
                ...$this->getPromotedConstructorsTreeNodes($node),
                ...$this->getPropertyFetchTreeNodes($node),
                ...$this->getMethodCallTreeNodes($node),
            ];

            array_map(function (TreeNode $treeNode) use ($from): void {
                $this->graph->addEdge($from, $treeNode);
            }, $relations);
        };
    }

    /**
     * Traverse node, and return 1 if node has not been visited yet
     *
     * @param TreeNode $node
     * @return int
     */
    private function traverse(TreeNode $node): int
    {
        if ($node->visited) {
            return 0;
        }
        $node->visited = true;
        array_map($this->traverse(...), $node->getAllNextBy());
        return 1;
    }

    /**
     * Returns the list of Graph TreeNode that must be added to the graph because there is a link from analyzed method
     * to property definitions identified thanks to promoted constructor.
     *
     * @param Node $node
     * @return array<TreeNode>
     */
    private function getPromotedConstructorsTreeNodes(Node $node): array
    {
        if (!$node instanceof Stmt\ClassMethod || '__construct' !== (string)$node->name) {
            return [];
        }

        // Promoted properties in constructors are defined by the presence of "public", "protected" or "private" flag.
        // Since PHP 8.1, the presence of "readonly" flag also promotes a property, with a "public" default visibility.
        $promotions = array_filter($node->getParams(), static function (Node\Param $param): bool {
            $mask = (Modifiers::VISIBILITY_MASK | Modifiers::READONLY);
            return (bool)($param->flags & $mask);
        });
        return array_map(function (Node\Param $param): TreeNode {
            /** @var Node\Expr\Variable $var Can't be an error as promoted properties are correctly parsed. */
            $var = $param->var;
            /** @var string $name Is mandatory to be a string as promoted properties cannot have expression as name. */
            $name = $var->name;
            return $this->graph->gather($name);
        }, $promotions);
    }

    /**
     * Returns the list of Graph TreeNode that must be added to the graph because there is a link from analyzed method
     * to property fetching, like in `$this->xxx`.
     *
     * @param Node $node
     * @return array<TreeNode>
     */
    private function getPropertyFetchTreeNodes(Node $node): array
    {
        if ($node instanceof Node\Expr\PropertyFetch && null !== ($nodeName = $this->readMemberNodeClearName($node))) {
            return [$this->graph->gather($nodeName)];
        }
        return [];
    }

    /**
     * Returns the list of Graph TreeNode that must be added to the graph because there is a link from analyzed method
     * to method calling, like in `$this->xxx()`.
     *
     * @param Node $node
     * @return array<TreeNode>
     */
    private function getMethodCallTreeNodes(Node $node): array
    {
        if (
            $node instanceof Node\Expr\MethodCall
            && !$node->var instanceof Node\Expr\New_ // Prevents `(new X())->methodCall()` issues.
            && null !== ($nodeName = $this->readMemberNodeClearName($node))
        ) {
            return [$this->graph->gather($nodeName . '()')];
        }
        return [];
    }

    /**
     * Reads the given node about a property or a method call to match its deterministic name.
     * It might not be a deterministic name if any of the following is true:
     * - variable has no name (usually due to a syntax error)
     * - variable name is a variable itself (prevents $a->$b(), $c(), or $a->$b)
     * - variable is not from "this"
     * - node name is not a string, but an expression (prevents $a->{$name} or $a->{<expr>})
     * In any of this case, this method returns NULL.
     *
     * @param Node\Expr\PropertyFetch|Node\Expr\MethodCall $node
     * @return string|null
     */
    private function readMemberNodeClearName(Node\Expr\PropertyFetch|Node\Expr\MethodCall $node): string|null
    {
        if (!isset($node->name)) {
            return null;
        }

        // No matter what the PHPDoc says, sometimes `$node->name` is actually a pure string.
        /** @var string|Node\Expr|Node\Identifier $nodeName */
        $nodeName = $node->name;

        if (
            property_exists($node->var, 'name')
            && !($node->var->name instanceof Node\Expr\Variable)
            && 'this' === (string)$node->var->name
            && ($nodeName instanceof Stringable || is_string($nodeName))
        ) {
            return (string)$nodeName;
        }
        return null;
    }
}
