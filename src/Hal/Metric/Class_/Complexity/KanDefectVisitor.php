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
use PhpParser\Node\Stmt\ClassLike;
use PhpParser\Node\Stmt\Do_;
use PhpParser\Node\Stmt\Foreach_;
use PhpParser\Node\Stmt\If_;
use PhpParser\Node\Stmt\Switch_;
use PhpParser\Node\Stmt\While_;
use PhpParser\NodeVisitorAbstract;

/**
 * Calculate Kan's defects
 *
 * Kan's defects = 0.15 + 0.23 *  number of do…while() + 0.22 * number of select() + 0.07 * number of if().
 * @package Hal\Metric\Class_\Complexity
 */
class KanDefectVisitor extends NodeVisitorAbstract
{
    use MetricsVisitorTrait;

    /**
     * @var float The Kan defect value. Starts to 0.15 and increase depending on the occurrences of some control
     *            structures like foreach, do…while, switch and if.
     */
    private $defect = 0.15;

    /**
     * Executed when leaving the traversing of the node. Used to calculates the following elements:
     * - Kan's defect
     * @param Node $node The current node to leave to make the analysis.
     * @return void
     */
    public function leaveNode(Node $node)
    {
        $class = $this->metrics->get(MetricClassNameGenerator::getName($node));

        if (!($node instanceof ClassLike) || null === $class) {
            return;
        }

        \iterate_over_node($node, [$this, 'incrementDefect']);

        $class->set('kanDefect', \round($this->defect, 2));
    }

    /**
     * Increments the Kan's defect value based on the following rules:
     * - "Do…while" and "foreach" worth 0.23
     * - "If" worth 0.07
     * - "Switch" worth 0.22
     * @param Node $node The given node to test.
     * @return void
     */
    protected function incrementDefect(Node $node)
    {
        $this->defect += [0.23, 0][$node instanceof Do_ || $node instanceof Foreach_ || $node instanceof While_];
        $this->defect += [0.07, 0][$node instanceof If_];
        $this->defect += [0.22, 0][$node instanceof Switch_];
    }
}
