<?php

namespace Hal\Metric\Class_\Complexity;

use Hal\Metric\Helper\MetricClassNameGenerator;
use Hal\Metric\Helper\RoleOfMethodDetector;
use Hal\Metric\Metrics;
use PhpParser\Node;
use PhpParser\Node\Stmt;
use PhpParser\NodeVisitorAbstract;

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
 * The weighted method count (WMC) is count of methods parameterized by a algorithm to compute the weight of a method.
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
 */
class CyclomaticComplexityVisitor extends NodeVisitorAbstract
{
    /** @var Metrics */
    private $metrics;

    public function __construct(Metrics $metrics)
    {
        $this->metrics = $metrics;
    }

    public function leaveNode(Node $node)
    {
        if ($node instanceof Stmt\Class_
            || $node instanceof Stmt\Interface_
            || $node instanceof Stmt\Trait_
        ) {
            $class = $this->metrics->get(MetricClassNameGenerator::getName($node));

            $ccn = 1;
            $wmc = 0;
            $ccnByMethod = [0]; // default maxMethodCcn if no methods are available

            $roleDetector = new RoleOfMethodDetector();

            foreach ($node->stmts as $stmt) {
                if ($stmt instanceof Stmt\ClassMethod) {

                    $role = $roleDetector->detects($stmt);
                    if (in_array($role, ['getter', 'setter'])) {
                        // We don't want to increase the CCN for getters and setters,
                        continue;
                    }

                    // iterate over children, recursively
                    $cb = function ($node) use (&$cb) {
                        $ccn = 0;

                        foreach (get_object_vars($node) as $name => $member) {
                            foreach (is_array($member) ? $member : [$member] as $memberItem) {
                                if ($memberItem instanceof Node) {
                                    $ccn += $cb($memberItem);
                                }
                            }
                        }
                        switch (true) {
                            case $node instanceof Stmt\If_:
                            case $node instanceof Stmt\ElseIf_:
                            case $node instanceof Stmt\For_:
                            case $node instanceof Stmt\Foreach_:
                            case $node instanceof Stmt\While_:
                            case $node instanceof Stmt\Do_:
                            case $node instanceof Node\Expr\BinaryOp\LogicalAnd:
                            case $node instanceof Node\Expr\BinaryOp\LogicalOr:
                            case $node instanceof Node\Expr\BinaryOp\LogicalXor:
                            case $node instanceof Node\Expr\BinaryOp\BooleanAnd:
                            case $node instanceof Node\Expr\BinaryOp\BooleanOr:
                            case $node instanceof Stmt\Catch_:
                            case $node instanceof Node\Expr\Ternary:
                            case $node instanceof Node\Expr\BinaryOp\Coalesce:
                                $ccn++;
                                break;
                            case $node instanceof Stmt\Case_: // include default
                                if ($node->cond !== null) { // exclude default
                                    $ccn++;
                                }
                                break;
                            case $node instanceof Node\Expr\BinaryOp\Spaceship:
                                $ccn += 2;
                                break;
                        }
                        return $ccn;
                    };

                    $methodCcn = $cb($stmt) + 1; // each method by default is CCN 1 even if it's empty

                    $wmc += $methodCcn;
                    $ccn += $methodCcn - 1;
                    $ccnByMethod[] = $methodCcn;
                }
            }

            $class->set('wmc', $wmc);
            $class->set('ccn', $ccn);
            $class->set('ccnMethodMax', max($ccnByMethod));
        }
    }
}
