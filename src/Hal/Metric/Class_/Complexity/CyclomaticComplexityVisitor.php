<?php
namespace Hal\Metric\Class_\Complexity;

use Hal\Component\Reflected\Method;
use Hal\Metric\Metrics;
use PhpParser\Node;
use PhpParser\Node\Stmt;
use PhpParser\NodeVisitorAbstract;

/**
 * Calculate cyclomatic complexity number
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
 */
class CyclomaticComplexityVisitor extends NodeVisitorAbstract
{

    /**
     * @var Metrics
     */
    private $metrics;

    /**
     * ClassEnumVisitor constructor.
     * @param Metrics $metrics
     */
    public function __construct(Metrics $metrics)
    {
        $this->metrics = $metrics;
    }

    /**
     * @inheritdoc
     */
    public function leaveNode(Node $node)
    {
        if ($node instanceof Stmt\Class_
            || $node instanceof Stmt\Interface_
        ) {

            $class = $this->metrics->get($node->namespacedName->toString());

            $ccn = 1;
            $ccnByMethod = array();

            foreach ($node->stmts as $stmt) {
                if ($stmt instanceof Stmt\ClassMethod) {

                    // iterate over children, recursively
                    $cb = function ($node) use (&$cb) {
                        $ccn = 0;
                        if (isset($node->stmts) && $node->stmts) {
                            foreach ($node->stmts as $child) {
                                $ccn += $cb($child);
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
                                $ccn = $ccn + 2;
                                break;
                        }
                        return $ccn;
                    };

                    $methodCcn = $cb($stmt);

                    $ccn += $methodCcn;
                    $ccnByMethod[] = $methodCcn + 1; // each method by default is CCN 1 even if it's empty
                }
            }

            $class->set('ccn', $ccn);

            $class->set('ccnMethodMax', 0);
            if (count($ccnByMethod)) {
                $class->set('ccnMethodMax', max($ccnByMethod));
            }
        }
    }
}
