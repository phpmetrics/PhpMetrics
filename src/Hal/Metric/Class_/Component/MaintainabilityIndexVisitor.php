<?php
namespace Hal\Metric\Class_\Component;

use Hal\Metric\FunctionMetric;
use Hal\Metric\Information\CommentLinesOfCode;
use Hal\Metric\Information\CommentWeight;
use Hal\Metric\Information\CyclomaticComplexity;
use Hal\Metric\Information\LinesOfCode;
use Hal\Metric\Information\LogicalLinesOfCode;
use Hal\Metric\Information\MaintainabilityIndex;
use Hal\Metric\Information\MaintainabilityIndexWithoutComments;
use Hal\Metric\Metrics;
//use Hoa\Ruler\Model\Bag\Scalar;
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
        if ($node instanceof Stmt\Class_) {

            if ($node instanceof Stmt\Class_) {
                $classOrFunction = $this->metrics->get($node->namespacedName->toString());
            } else {
                $classOrFunction = new FunctionMetric($node->name);
                $this->metrics->attach($classOrFunction);
            }
            if (null === $lloc = $classOrFunction->get(LogicalLinesOfCode::ID)) {
                throw new \LogicException('please enable length (lloc) visitor first');
            }
            if (null === $cloc = $classOrFunction->get(CommentLinesOfCode::ID)) {
                throw new \LogicException('please enable length (cloc) visitor first');
            }
            if (null === $loc = $classOrFunction->get(LinesOfCode::ID)) {
                throw new \LogicException('please enable length (loc) visitor first');
            }
            if (null === $ccn = $classOrFunction->get(CyclomaticComplexity::ID)) {
                throw new \LogicException('please enable McCabe visitor first');
            }
            if (null === $volume = $classOrFunction->get('volume')) {
                throw new \LogicException('please enable Halstead visitor first');
            }

            // maintainability index without comment
            $MIwoC = max(
                (171
                    - (5.2 * \log($volume))
                    - (0.23 * $ccn)
                    - (16.2 * \log($lloc))
                ) * 100 / 171
                , 0);
            if (is_infinite($MIwoC)) {
                $MIwoC = 171;
            }

            // comment weight
            if ($loc > 0) {
                $CM = $cloc / $loc;
                $commentWeight = 50 * sin(sqrt(2.4 * $CM));
            } else {
                $commentWeight = 0;
            }

            // maintainability index
            $mi = $MIwoC + $commentWeight;

            // save result
            $classOrFunction
                ->set(MaintainabilityIndex::ID, round($mi, 2))
                ->set(MaintainabilityIndexWithoutComments::ID, round($MIwoC, 2))
                ->set(CommentWeight::ID, round($commentWeight, 2));
            $this->metrics->attach($classOrFunction);
        }
    }
}
