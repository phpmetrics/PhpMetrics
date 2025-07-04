<?php

namespace Hal\Metric\Class_\Component;

use Hal\Component\Ast\NodeTyper;
use Hal\Metric\FunctionMetric;
use Hal\Metric\Metrics;
use Hoa\Ruler\Model\Bag\Scalar;
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
 */
class MaintainabilityIndexVisitor extends NodeVisitorAbstract
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
        if (NodeTyper::isOrganizedLogicalClassStructure($node)) {
            $name = getNameOfNode($node);
            $classOrFunction = $this->metrics->get($name);

            if(null === $classOrFunction) {
                throw new \LogicException('class or function ' . $name . ' not found in metrics');
            }

            if (null === $lloc = $classOrFunction->get('lloc')) {
                throw new \LogicException('please enable length (lloc) visitor first');
            }
            if (null === $cloc = $classOrFunction->get('cloc')) {
                throw new \LogicException('please enable length (cloc) visitor first');
            }
            if (null === $loc = $classOrFunction->get('loc')) {
                throw new \LogicException('please enable length (loc) visitor first');
            }
            if (null === $ccn = $classOrFunction->get('ccn')) {
                throw new \LogicException('please enable McCabe visitor first');
            }
            if (null === $volume = $classOrFunction->get('volume')) {
                throw new \LogicException('please enable Halstead visitor first');
            }

            // maintainability index without comment
            $MIwoC = max(
                (
                    171
                    - (5.2 * \log($volume))
                    - (0.23 * $ccn)
                    - (16.2 * \log($lloc))
                ) * 100 / 171,
                0
            );
            if (is_infinite($MIwoC)) {
                $MIwoC = 171;
            }

            // comment weight
            if ($loc > 0) {
                $CM = $cloc / $loc;
                $commentWeight = 50 * sin(sqrt(2.4 * $CM));
            }

            // maintainability index
            $mi = $MIwoC + $commentWeight;

            // save result
            $classOrFunction
                ->set('mi', round($mi, 2))
                ->set('mIwoC', round($MIwoC, 2))
                ->set('commentWeight', round($commentWeight, 2));
            $this->metrics->attach($classOrFunction);
        }
    }
}
