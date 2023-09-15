<?php
declare(strict_types=1);

namespace Hal\Metric\Class_\Complexity;

use Hal\Metric\FunctionMetric;
use Hal\Metric\Helper\DetectorInterface;
use Hal\Metric\Helper\MetricNameGenerator;
use Hal\Metric\Metric;
use Hal\Metric\Metrics;
use PhpParser\Node;
use PhpParser\Node\Stmt;
use PhpParser\NodeVisitorAbstract;
use function array_column;
use function array_combine;
use function array_filter;
use function array_key_exists;
use function array_map;
use function array_sum;
use function get_object_vars;
use function in_array;
use function is_array;
use function max;

/**
 * Calculate cyclomatic complexity number and weighted method count.
 *
 * The cyclomatic complexity (CCN) is a measure of control structure complexity of a function or procedure.
 * We can calculate ccn in two ways (we choose the second):
 *
 *  1.  Cyclomatic complexity (CCN) = E - N + 2P
 *      Where:
 *      P = number of disconnected parts of the flow graph (e.g. a calling program and a subroutine)
 *      E = number of edges (transfers of control)
 *      N = number of nodes (sequential group of statements containing only one transfer of control)
 *
 *  2. CCN = Number of each decision point
 *
 * The weighted method count (WMC) is count of methods parameterized by an algorithm to compute the weight of a method.
 * Given a weight metric w and methods m it can be computed as
 *
 *  sum m(w') over (w' in w)
 *
 * Possible algorithms are:
 *
 *  - Cyclomatic Complexity
 *  - Lines of Code
 *  - 1 (unweighted WMC)
 *
 * This visitor provides two metrics, the maximal CCN of all methods from one class (currently stored as ccnMethodMax)
 * and the WMC using the CCN as weight metric (currently stored as ccn).
 *
 * @see https://en.wikipedia.org/wiki/Cyclomatic_complexity
 * @see http://www.literateprogramming.com/mccabe.pdf
 * @see https://www.pitt.edu/~ckemerer/CK%20research%20papers/MetricForOOD_ChidamberKemerer94.pdf
 *
 * @phpstan-type ComplexIncrementCCNode callable(Node): int
 */
final class CyclomaticComplexityVisitor extends NodeVisitorAbstract
{
    /** @var array<string, int> List of values about how much the CC must be incremented for each type of node. */
    private static array $simpleIncrementList = [
        'Stmt_If' => 1, // `if (...)`
        'Stmt_ElseIf' => 1, // `elseif (...)`
        'Stmt_For' => 1, // `for (...)`
        'Stmt_Foreach' => 1, // `foreach (...)`
        'Stmt_While' => 1, // `while (...) { ... }`
        'Stmt_Do' => 1, // `do { ... } while (...)`
        'Expr_BinaryOp_LogicalAnd' => 1, // `... and ...`
        'Expr_BinaryOp_LogicalOr' => 1, // `... or ...`
        'Expr_BinaryOp_LogicalXor' => 1, // `... xor ...`
        'Expr_BinaryOp_BooleanAnd' => 1, // `... && ...`
        'Expr_BinaryOp_BooleanOr' => 1, // `... || ...`
        'Stmt_Catch' => 1, // `... } catch (...) { ...`
        'Expr_Ternary' => 1, // `... ? ... : ...`
        'Expr_BinaryOp_Coalesce' => 1, // `... ?? ...`
        'Expr_NullsafeMethodCall' => 1, // `$x?->y()`
        'Expr_NullsafePropertyFetch' => 1, // `$x?->y`
        'Expr_BinaryOp_Spaceship' => 2, // `... <=> ...`
    ];

    /** @var array<string, ComplexIncrementCCNode> List of callbacks defining the increment value for some nodes. */
    private static array $complexIncrementList;

    /**
     * @param Metrics $metrics
     * @param DetectorInterface $roleOfMethodDetector
     */
    public function __construct(
        private readonly Metrics $metrics,
        private readonly DetectorInterface $roleOfMethodDetector
    ) {
        // Callbacks cannot be used in class declarations (yet?)
        /** @phpstan-ignore-next-line Polymorphic calls are handled in self::calculateCC method. */
        self::$complexIncrementList = [
            // `case ...:` from a `switch`. Ignore `default:`.
            'Stmt_Case' => static fn (Stmt\Case_ $node): int => (null !== $node->cond) ? 1 : 0,
            // `... => ...` from a `match`. Ignore `default =>`.
            'MatchArm' => static fn (Node\MatchArm $node): int => count((array)$node->conds)
        ];
    }

    /**
     * {@inheritDoc}
     */
    public function leaveNode(Node $node): null|int|Node|array // TODO PHP 8.2: only return null here.
    {
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

        $allMethods = $this->discoverMethods($node->getMethods());
        // We don't want to increase the CCN of the class for getters and setters.
        $methods = array_filter($allMethods, static fn (array $method): bool => !$method['isAccessor']);
        $ccByMethods = array_column($methods, 'ccn');
        $weightMethodCount = array_sum($ccByMethods);
        $classCC = 1 + $weightMethodCount - count($methods);

        $class->set('wmc', $weightMethodCount);
        $class->set('ccn', $classCC);
        $class->set('ccnMethodMax', max([0, ...$ccByMethods]));

        // Apply the CCN of the method for each method found in the class.
        /** @var array<FunctionMetric> $classMethods */
        $classMethods = $class->get('methods'); // $class->get('methods') is defined in ClassEnumVisitor.
        array_map(static function (FunctionMetric $method) use ($allMethods): void {
            $methodName = $method->getName();
            $method->set('ccn', $allMethods[$methodName]['ccn']);
            $method->set('isAccessor', $allMethods[$methodName]['isAccessor']);
        }, $classMethods);
        return null;
    }

    /**
     * Calculates the cyclomatic complexity over a given Node.
     * Recursively enters into each sub-node to calculate each related cyclomatic complexity, and sum them.
     *
     * @param Node $node
     * @return int
     */
    private function calculateCC(Node $node): int
    {
        $cyclomaticComplexity = 0;

        foreach (get_object_vars($node) as $member) {
            foreach (is_array($member) ? $member : [$member] as $memberItem) {
                if ($memberItem instanceof Node) {
                    $cyclomaticComplexity += $this->calculateCC($memberItem);
                }
            }
        }

        $type = $node->getType();

        if (array_key_exists($type, self::$simpleIncrementList)) {
            return $cyclomaticComplexity + self::$simpleIncrementList[$type];
        }
        if (array_key_exists($type, self::$complexIncrementList)) {
            return $cyclomaticComplexity + self::$complexIncrementList[$type]($node);
        }
        return $cyclomaticComplexity;
    }

    /**
     * Discover the CCN for all given methods and keep information if the method is an accessor or not.
     *
     * @param array<Stmt\ClassMethod> $stmts Lost of methods
     * @return array<string, array{ccn: int, isAccessor: bool}> Data calculated by the discovering.
     */
    private function discoverMethods(array $stmts): array
    {
        $methodsNames = array_map(static fn (Stmt\ClassMethod $stmt): string => $stmt->name->toString(), $stmts);
        $methodsData = array_map(function (Stmt\ClassMethod $stmt): array {
            $isAccessor = in_array($this->roleOfMethodDetector->detects($stmt), ['getter', 'setter'], true);
            return [
                // Each method by default is CCN 1 even if it's empty. Ignoring accessors and give them the default CCN.
                'ccn' => $isAccessor ? 1 : $this->calculateCC($stmt) + 1,
                'isAccessor' => $isAccessor
            ];
        }, $stmts);

        return array_combine($methodsNames, $methodsData);
    }
}
