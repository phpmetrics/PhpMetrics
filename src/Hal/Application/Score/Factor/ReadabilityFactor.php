<?php

/*
* (c) Jean-François Lépine <https://twitter.com/Halleck45>
*
* For the full copyright and license information, please view the LICENSE
* file that was distributed with this source code.
*/

namespace Hal\Application\Score\Factor;

use Hal\Application\Score\Calculator;
use Hal\Component\Bounds\Result\ResultInterface;
use Hal\Component\Result\ResultCollection;

/**
 * Is the code accessible for new developers ?
 *
 * @author Jean-François Lépine <https://twitter.com/Halleck45>
 */
class ReadabilityFactor implements FactorInterface {

    /**
     * Bounds
     *
     * @var Calculator
     */
    private $calculator;

    /**
     * Constructor
     *
     * @param Calculator $calculator
     */
    public function __construct(Calculator $calculator)
    {
        $this->calculator = $calculator;
    }

    /**
     * @inheritdoc
     */
    public function calculate(ResultCollection $collection, ResultCollection $groupedResults, ResultInterface $bound) {
        $notes = array(
            $this->calculator->lowIsBetter(5.8, 18, $bound->getAverage('difficulty'))
            , $this->calculator->highIsBetter(42, 32, $bound->getAverage('commentWeight'))
        );
        return round(array_sum($notes) / count($notes, COUNT_NORMAL), 2);
    }

    /**
     * @inheritedDoc
     */
    public function getName() {
        return 'Accessibility';
    }
}