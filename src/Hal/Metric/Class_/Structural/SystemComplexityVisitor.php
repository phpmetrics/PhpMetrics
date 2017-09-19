<?php
namespace Hal\Metric\Class_\Structural;

use Hal\Metric\Helper\MetricClassNameGenerator;
use Hal\Metric\Metrics;
use PhpParser\Node;
use PhpParser\Node\Stmt;
use PhpParser\NodeVisitorAbstract;

/**
 * Calculates Card And Agresti metric
 *
 *      Fan-out = Structural fan-out = Number of other procedures this procedure calls
 *      v = number of input/output variables for a procedure
 *
 *      (SC) Structural complexity = fan-out^2
 *      (DC) Data complexity = v / (fan-out + 1)
 *
 * @author Jean-François Lépine <https://twitter.com/Halleck45>
 */
class SystemComplexityVisitor extends NodeVisitorAbstract
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
        if ($node instanceof Stmt\Class_ || $node instanceof Stmt\Trait_) {

            $class = $this->metrics->get(MetricClassNameGenerator::getName($node));

            $sy = $dc = $sc = array();

            foreach ($node->stmts as $stmt) {
                if ($stmt instanceof Stmt\ClassMethod) {

                    // number of returns and calls
                    $output = 0;
                    $fanout = [];

                    iterate_over_node($node, function ($node) use (&$output, &$fanout) {
                        switch (true) {
                            case $node instanceof Stmt\Return_:
                                $output++;
                                break;
                            case $node instanceof Node\Expr\StaticCall:
                            case $node instanceof Node\Expr\MethodCall:
                                array_push($fanout, getNameOfNode($node));
                        }
                    });

                    $fanout = sizeof(array_unique($fanout));
                    $v = sizeof($stmt->params) + $output;
                    $ldc = $v / ($fanout + 1);
                    $lsc = pow($fanout, 2);
                    $sy[] = $ldc + $lsc;
                    $dc[] = $ldc;
                    $sc[] = $lsc;
                }
            }

            // average for class
            $class
                ->set('relativeStructuralComplexity', empty($sc) ? 0 : round(array_sum($sc) / sizeof($sc), 2))
                ->set('relativeDataComplexity', empty($dc) ? 0 : round(array_sum($dc) / sizeof($dc), 2))
                ->set('relativeSystemComplexity', empty($sy) ? 0 : round(array_sum($sy) / sizeof($sy), 2))
                ->set('totalStructuralComplexity', round(array_sum($sc), 2))
                ->set('totalDataComplexity', round(array_sum($dc), 2))
                ->set('totalSystemComplexity', round(array_sum($dc) + array_sum($sc), 2));
        }
    }
}
