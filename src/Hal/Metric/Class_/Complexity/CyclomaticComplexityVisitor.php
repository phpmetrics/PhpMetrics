<?php
/**
 * (c) Jean-François Lépine <https://twitter.com/Halleck45>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Hal\Metric\Class_\Complexity;

use Hal\Metric\Helper\MetricClassNameGenerator;
use Hal\Metric\MetricsVisitorTrait;
use PhpParser\Node;
use PhpParser\Node\Stmt;
use PhpParser\Node\Stmt\ClassMethod;
use PhpParser\NodeVisitorAbstract;

/**
 * Class CyclomaticComplexityVisitor
 * Calculate cyclomatic complexity number.
 *
 * We can calculate ccn in two ways (we choose the second):
 *
 *  1.  Cyclomatic complexity (CC) = E - N + 2P
 *      Where:
 *      P = number of disconnected parts of the flow graph (e.g. a calling program and a subroutine)
 *      E = number of edges (transfers of control)
 *      N = number of nodes (sequential group of statements containing only one transfer of control)
 *
 * 2. CC = Number of each decision point
 *
 * @package Hal\Metric\Class_\Complexity
 */
class CyclomaticComplexityVisitor extends NodeVisitorAbstract
{
    use MetricsVisitorTrait;

    /**
     * Executed when leaving the traversing of the node. Used to calculates the following elements:
     * - Cyclomatic complexity of the class
     * - Highest cyclomatic complexity found for a method in the current class
     * @param Node $node The current node to leave to make the analysis.
     * @return void
     */
    public function leaveNode(Node $node)
    {
        $class = $this->metrics->get(MetricClassNameGenerator::getName($node));

        if (!($node instanceof Stmt\ClassLike) || null === $class) {
            return;
        }

        $ccn = 1;
        $ccnMethodMax = 0;

        foreach ($node->stmts as $stmt) {
            if (!($stmt instanceof ClassMethod)) {
                // Ignore statements that are not methods.
                continue;
            }

            // Iterate over children, recursively
            $methodCcn = $this->countCyclomaticComplexity($stmt);

            $ccn += $methodCcn;
            $ccnMethodMax = \max($ccnMethodMax, $methodCcn + 1); // each method by default is CCN 1 even if it's empty
        }

        $class->set('ccn', $ccn);
        $class->set('ccnMethodMax', $ccnMethodMax);
    }

    /**
     * Use recursion to count the cyclomatic complexity of the given node.
     * @param Node $node
     * @return int
     */
    private function countCyclomaticComplexity(Node $node)
    {
        $ccn = 0;
        if (!empty($node->stmts)) {
            foreach ($node->stmts as $child) {
                $ccn += $this->countCyclomaticComplexity($child);
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
            case $node instanceof Node\Expr\BinaryOp\BooleanAnd:
            case $node instanceof Node\Expr\BinaryOp\BooleanOr:
            case $node instanceof Node\Expr\BinaryOp\Spaceship:
            case $node instanceof Stmt\Case_: // include default
            case $node instanceof Stmt\Catch_:
            case $node instanceof Stmt\Continue_:
                $ccn++;
                break;
            case $node instanceof Node\Expr\Ternary:
            case $node instanceof Node\Expr\BinaryOp\Coalesce:
                $ccn += 2;
                break;
        }

        return $ccn;
    }
}
