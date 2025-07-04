<?php

namespace Hal\Metric\Class_\Text;

use Hal\Component\Ast\NodeTyper;
use Hal\Metric\FunctionMetric;
use Hal\Metric\Metrics;
use Hoa\Ruler\Model\Bag\Scalar;
use PhpParser\Node;
use PhpParser\Node\Stmt;
use PhpParser\NodeVisitorAbstract;

/**
 * Calculates Halstead complexity
 *
 *      According Wikipedia, "Halstead complexity measures are software metrics introduced by Maurice Howard Halstead in
 *      1977 as part of his treatise on establishing an empirical science of software development.
 *      Halstead makes the observation that metrics of the software should reflect the implementation or
 *      expression of algorithms in different languages, but be independent of their execution on a specific platform.
 *      These metrics are therefore computed statically from the code."
 *
 * @author Jean-François Lépine <https://twitter.com/Halleck45>
 * @package Hal\Metric\Class_\Coupling
 */
class HalsteadVisitor extends NodeVisitorAbstract
{
    /**
     * @var Metrics
     */
    private $metrics;

    /**
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
        if (
            NodeTyper::isOrganizedLogicalClassStructure($node)
            || $node instanceof Stmt\Function_
        ) {
            if (NodeTyper::isOrganizedLogicalClassStructure($node) ) {
                $name = getNameOfNode($node);
                $classOrFunction = $this->metrics->get($name);
            } else {
                $classOrFunction = new FunctionMetric((string) $node->name);
                $this->metrics->attach($classOrFunction);
            }

            // search for operands and operators
            $operands = [];
            $operators = [];

            iterate_over_node($node, function ($node) use (&$operators, &$operands) {
                if ($node instanceof Node\Expr\BinaryOp
                    || $node instanceof Node\Expr\AssignOp
                    || $node instanceof Stmt\If_
                    || $node instanceof Stmt\For_
                    || $node instanceof Stmt\Switch_
                    || $node instanceof Stmt\Catch_
                    || $node instanceof Stmt\Return_
                    || $node instanceof Stmt\While_
                    || $node instanceof Node\Expr\Assign
                ) {
                    // operators
                    array_push($operators, get_class($node));
                }

                // nicik/php-parser:^4
                if ($node instanceof Node\Param
                    && isset($node->var)
                    && $node->var instanceof Node\Expr\Variable
                ) {
                    return;
                }

                if ($node instanceof Node\Expr\Cast
                    || $node instanceof Node\Expr\Variable
                    || $node instanceof Node\Param
                    || $node instanceof Node\Scalar
                ) {
                    // operands
                    if (isset($node->value)) {
                        $name = $node->value;
                    } elseif (isset($node->name)) {
                        $name = $node->name;
                    } else {
                        $name = get_class($node);
                    }
                    array_push($operands, $name);
                }
            });

            // calculate halstead metrics
            $uniqueOperators = array_map('unserialize', array_unique(array_map('serialize', $operators)));
            $uniqueOperands = array_map('unserialize', array_unique(array_map('serialize', $operands)));

            $n1 = count($uniqueOperators, COUNT_NORMAL);
            $n2 = count($uniqueOperands, COUNT_NORMAL);
            $N1 = count($operators, COUNT_NORMAL);
            $N2 = count($operands, COUNT_NORMAL);

            if (($n2 == 0) || ($N2 == 0)) {
                // files without operators
                $V = $n1 = $n2 = $N1 = $N2 = $E = $D = $B = $T = $I = $L = 0;
            } else {
                $devAbility = 3000;
                $N = $N1 + $N2;
                $n = $n1 + $n2;
                $V = $N * log($n, 2);
                $L = (2 / max(1, $n1)) * ($n2 / $N2);
                $D = ($n1 / 2) * ($N2 / $n2);
                $E = $V * $D;
                $B = $V / $devAbility;
                $T = $E / 18;
                $I = $L * $V;
            }

            // save result
            $classOrFunction
                ->set('length', $N1 + $N2)
                ->set('vocabulary', $n1 + $n2)
                ->set('volume', round($V, 2))
                ->set('difficulty', round($D, 2))
                ->set('effort', round($E, 2))
                ->set('level', round($L, 2))
                ->set('bugs', round($B, 2))
                ->set('time', round($T))
                ->set('intelligentContent', round($I, 2))
                ->set('number_operators', $N1)
                ->set('number_operands', $N2)
                ->set('number_operators_unique', $n1)
                ->set('number_operands_unique', $n2);
            $this->metrics->attach($classOrFunction);
        }
    }
}
