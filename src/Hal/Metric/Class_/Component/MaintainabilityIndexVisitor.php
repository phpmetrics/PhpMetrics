<?php
/**
 * (c) Jean-François Lépine <https://twitter.com/Halleck45>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Hal\Metric\Class_\Component;

use Hal\Metric\Helper\MetricClassNameGenerator;
use Hal\Metric\Metric;
use Hal\Metric\MetricException;
use Hal\Metric\MetricsVisitorTrait;
use PhpParser\Node;
use PhpParser\Node\Stmt;
use PhpParser\NodeVisitorAbstract;

/**
 * Calculates Maintainability Index
 *
 *      According to Wikipedia, "Maintainability Index is a software metric which measures how maintainable (easy to
 *      support and change) the source code is. The maintainability index is calculated as a factored formula consisting
 *      of Lines Of Code, Cyclomatic Complexity and Halstead volume."
 *
 *      MIwoc: Maintainability Index without comments
 *      MIcw: Maintainability Index comment weight
 *      MI: Maintainability Index = MIwoc + MIcw
 *
 *      MIwoc = 171 - 5.2 * ln(aveV) -0.23 * aveG -16.2 * ln(aveLOC)
 *      MIcw = 50 * sin(sqrt(2.4 * perCM))
 *      MI = MIwoc + MIcw
 *
 * @author Jean-François Lépine <https://twitter.com/Halleck45>
 * @package Hal\Metric\Class_\Component
 */
class MaintainabilityIndexVisitor extends NodeVisitorAbstract
{
    use MetricsVisitorTrait;

    /**
     * Executed when leaving the traversing of the node. Used to calculates the following elements:
     * - Maintainability index
     * - Maintainability index without comments
     * - Comments weight
     *
     * This visitor must be called after the following visitors:
     * - LengthVisitor
     * - CyclomaticComplexityVisitor
     * - HalsteadVisitor
     *
     * @param Node $node The current node to leave to make the analysis.
     * @return void
     */
    public function leaveNode(Node $node)
    {
        $class = $this->metrics->get(MetricClassNameGenerator::getName($node));

        if (!($node instanceof Stmt\Class_ || $node instanceof Stmt\Trait_) || null === $class) {
            return;
        }

        list($loc, $cLoc, $lLoc, $ccn, $volume) = $this->checkVisitors($class);

        // Calculate the maintainability index without any comments.
        $MIwoC = \max((171 - (5.2 * \log($volume)) - (0.23 * $ccn) - (16.2 * \log($lLoc))) * 100 / 171, 0);
        $MIwoC = [$MIwoC, 171][\is_infinite($MIwoC)];

        // Calculate the comments weight.
        $commentWeight = 50 * \sin(\sqrt(2.4 * $cLoc / [$loc, 1][$loc > 0]));

        // Calculate the maintainability index.
        $mi = $MIwoC + $commentWeight;

        $class
            ->set('mi', \round($mi, 2))
            ->set('mIwoC', \round($MIwoC, 2))
            ->set('commentWeight', \round($commentWeight, 2));
        $this->metrics->attach($class);
    }

    /**
     * Check all required visitors and return the values stored thanks to them required for the current visitor.
     * @param Metric $class
     * @return array
     */
    private function checkVisitors(Metric $class)
    {
        if ((null === ($loc = $class->get('loc')))
            || (null === ($cLoc = $class->get('cloc')))
            || (null === ($lLoc = $class->get('lloc')))
        ) {
            throw MetricException::disabledLengthVisitor();
        }

        if (null === ($ccn = $class->get('ccn'))) {
            throw MetricException::disabledCyclomaticComplexityVisitor();
        }
        if (null === ($volume = $class->get('volume'))) {
            throw MetricException::disabledHalsteadVisitor();
        }

        return [$loc, $cLoc, $lLoc, $ccn, $volume];
    }
}
