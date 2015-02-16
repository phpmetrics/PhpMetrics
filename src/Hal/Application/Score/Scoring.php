<?php

/*
* (c) Jean-François Lépine <https://twitter.com/Halleck45>
*
* For the full copyright and license information, please view the LICENSE
* file that was distributed with this source code.
*/

namespace Hal\Application\Score;

use Hal\Application\Score\Factor\ReadabilityFactor;
use Hal\Application\Score\Factor\BugPreventingFactor;
use Hal\Application\Score\Factor\ComplexityFactor;
use Hal\Application\Score\Factor\MaintainabilityFactor;
use Hal\Application\Score\Factor\VolumeFactor;
use Hal\Component\Bounds\BoundsInterface;
use Hal\Component\Result\ResultCollection;
use Hal\Component\Score\ScoringInterface;

/**
 * Calculate score
 *
 * @author Jean-François Lépine <https://twitter.com/Halleck45>
 */
class Scoring implements ScoringInterface{

    /**
     * Maximal score
     */
    const MAX = 100;

    /**
     * Bounds
     *
     * @var BoundsInterface
     */
    private $bound;

    /**
     * Constructor
     *
     * @param BoundsInterface $bound
     */
    public function __construct(BoundsInterface $bound)
    {
        $this->bound = $bound;
    }

    /**
     * @inheritdoc
     */
    public function calculate(ResultCollection $collection, ResultCollection $groupedResults) {

        $calculator = new Calculator();
        $bound = $this->bound->calculate($collection);

        // list of factors of quality
        $factors = array(
            new MaintainabilityFactor($calculator)
            , new ReadabilityFactor($calculator)
            , new ComplexityFactor($calculator)
            , new VolumeFactor($calculator)
            , new BugPreventingFactor($calculator)
        );

        // score
        $result = new Result;
        foreach($factors as $qualityFactor) {
            $score = $qualityFactor->calculate($collection, $groupedResults, $bound);
            $result->push($qualityFactor->getName(), $score);
        }

        return $result;
    }
}