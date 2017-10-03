<?php
/**
 * (c) Jean-François Lépine <https://twitter.com/Halleck45>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Hal\Metric\Class_\Structural;

use Hal\Metric\Helper\MetricClassNameGenerator;
use Hal\Metric\MetricsVisitorTrait;
use PhpParser\Node;
use PhpParser\Node\Stmt;
use PhpParser\NodeVisitorAbstract;

/**
 * Class SystemComplexityVisitor
 * Calculates Card And Agresti metric
 *
 *      Fan-out = Structural fan-out = Number of other procedures this procedure calls
 *      v = number of input/output variables for a procedure
 *
 *      (SC) Structural complexity = fan-out^2
 *      (DC) Data complexity = v / (fan-out + 1)
 *      (SY) System complexity = SC + DC
 *
 * @author Jean-François Lépine <https://twitter.com/Halleck45>
 * @package Hal\Metric\Class_\Structural
 */
class SystemComplexityVisitor extends NodeVisitorAbstract
{
    use MetricsVisitorTrait;

    /**
     * Executed when leaving the traversing of the node. Used to calculates the following elements:
     * - Relative and total structural complexity
     * - Relative and total data complexity
     * - Relative and total system complexity
     * @param Node $node The current node to leave to make the analysis.
     * @return void
     */
    public function leaveNode(Node $node)
    {
        if (!($node instanceof Stmt\Class_ || $node instanceof Stmt\Trait_)
            || (null === ($class = $this->metrics->get(MetricClassNameGenerator::getName($node))))
        ) {
            return;
        }

        $sy = $dc = $sc = [];

        foreach ($node->stmts as $stmt) {
            if (!($stmt instanceof Stmt\ClassMethod)) {
                continue;
            }

            // number of returns and calls
            $output = 0;
            $fanOut = [];

            \iterate_over_node($node, function ($node) use (&$output, &$fanOut) {
                switch (true) {
                    case $node instanceof Stmt\Return_:
                        $output++;
                        break;
                    case $node instanceof Node\Expr\StaticCall:
                    case $node instanceof Node\Expr\MethodCall:
                        $fanOut[] = \getNameOfNode($node);
                }
            });

            $fanOut = \count(\array_unique($fanOut));
            $v = \count($stmt->params) + $output;
            $ldc = $v / ($fanOut + 1);
            $lsc = \pow($fanOut, 2);
            $sy[] = $ldc + $lsc;
            $dc[] = $ldc;
            $sc[] = $lsc;
        }

        $sumSc = \array_sum($sc);
        $sumDc = \array_sum($dc);
        $sumSy = \array_sum($sy);

        // Averages and totals for class or trait.
        $class
            ->set('relativeStructuralComplexity', \round($sumSc / \count($sc) ?: 1, 2))
            ->set('relativeDataComplexity', \round($sumDc / \count($dc) ?: 1, 2))
            ->set('relativeSystemComplexity', \round($sumSy / \count($sy) ?: 1, 2))
            ->set('totalStructuralComplexity', \round($sumSc, 2))
            ->set('totalDataComplexity', \round($sumDc, 2))
            ->set('totalSystemComplexity', \round($sumSy, 2));
    }
}
