<?php
/**
 * (c) Jean-François Lépine <https://twitter.com/Halleck45>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Hal\Metric\Class_\Text;

use Hal\Metric\FunctionMetric;
use Hal\Metric\Helper\MetricClassNameGenerator;
use Hal\Metric\MetricsVisitorTrait;
use PhpParser\Node;
use PhpParser\Node\Stmt;
use PhpParser\NodeVisitorAbstract;

/**
 * Class HalsteadVisitor
 * Calculates Halstead complexity.
 *
 *      According Wikipedia, "Halstead complexity measures are software metrics introduced by Maurice Howard Halstead in
 *      1977 as part of his treatise on establishing an empirical science of software development.
 *      Halstead makes the observation that metrics of the software should reflect the implementation or
 *      expression of algorithms in different languages, but be independent of their execution on a specific platform.
 *      These metrics are therefore computed statically from the code."
 *
 * @author Jean-François Lépine <https://twitter.com/Halleck45>
 * @package Hal\Metric\Class_\Text
 */
class HalsteadVisitor extends NodeVisitorAbstract
{
    use MetricsVisitorTrait;

    /**
     * Executed when leaving the traversing of the node. Used to calculates the following elements:
     * - Number of operators and operands (N1 and N2)
     * - Number of unique operators and operands (n1 and n2)
     * - Length and vocabulary according to Halstead calculations (N = N1 + N2 and n = n1 + n2)
     * - The Halstead volume (V = N * log₂(n))
     * - The Halstead level (L = (2 / n1) * (n2 / N2) [n1 > 0])
     * - The Halstead difficulty (D = (n1 / 2) * (N2 / n2))
     * - The Halstead effort and bugs (E = V * D and B = V / 3000)
     * - The Halstead time and intelligence (T = E / 18 and I = L * V)
     * @param Node $node The current node to leave to make the analysis.
     * @return void
     */
    public function leaveNode(Node $node)
    {
        if (!($node instanceof Stmt\Class_ || $node instanceof Stmt\Function_ || $node instanceof Stmt\Trait_)) {
            return;
        }

        if ($node instanceof Stmt\Function_) {
            $classOrFunction = new FunctionMetric($node->name);
            $this->metrics->attach($classOrFunction);
        } else {
            $classOrFunction = $this->metrics->get(MetricClassNameGenerator::getName($node));
        }

        // Search for operands and operators.
        $operands = [];
        $operators = [];

        \iterate_over_node($node, function ($node) use (&$operators, &$operands) {

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
                // Operator
                $operators[] = \get_class($node);
                return;
            }

            if ($node instanceof Node\Expr\Cast
                || $node instanceof Node\Expr\Variable
                || $node instanceof Node\Param
                || $node instanceof Node\Scalar
            ) {
                // Operand
                // $name is the nodeValue if exists, or the nodeName if exists, or the className of the node.
                $name = (isset($node->value) ? $node->value : (isset($node->name) ? $node->name : \get_class($node)));
                $operands[] = $name;
            }
        });

        // Calculating the Halstead metrics.
        $uniqueOperators = \array_map('\unserialize', \array_unique(\array_map('\serialize', $operators)));
        $uniqueOperands = \array_map('\unserialize', \array_unique(\array_map('\serialize', $operands)));

        $n1 = \count($uniqueOperators);
        $n2 = \count($uniqueOperands);
        $N1 = \count($operators);
        $N2 = \count($operands);

        // Reset all values if the files have no operators.
        if (0 === $n2 * $N2) {
            // files without operators
            $V = $n1 = $n2 = $N1 = $N2 = $E = $D = $B = $T = $I = $L = 0;
        } else {
            $devAbility = 3000;
            $N = $N1 + $N2;
            $n = $n1 + $n2;
            $V = $N * \log($n, 2);
            $L = (2 / \max(1, $n1)) * ($n2 / $N2);
            $D = ($n1 / 2) * ($N2 / $n2);
            $E = $V * $D;
            $B = $V / $devAbility;
            $T = $E / 18;
            $I = $L * $V;
        }

        // Save the result.
        $classOrFunction
            ->set('length', $N1 + $N2)
            ->set('vocabulary', $n1 + $n2)
            ->set('volume', \round($V, 2))
            ->set('difficulty', \round($D, 2))
            ->set('effort', \round($E, 2))
            ->set('level', \round($L, 2))
            ->set('bugs', \round($B, 2))
            ->set('time', \round($T))
            ->set('intelligentContent', \round($I, 2))
            ->set('number_operators', $N1)
            ->set('number_operands', $N2)
            ->set('number_operators_unique', $n1)
            ->set('number_operands_unique', $n2);
        $this->metrics->attach($classOrFunction);
    }
}
