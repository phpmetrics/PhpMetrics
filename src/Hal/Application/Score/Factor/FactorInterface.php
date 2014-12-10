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
 * PrincipleInterface
 *
 * @author Jean-François Lépine <https://twitter.com/Halleck45>
 */
interface FactorInterface {

    /**
     * Give score for principle
     *
     * @param ResultCollection $collection
     * @param ResultCollection $groupedResults
     * @param ResultInterface $bound
     * @return mixed
     */
    public function calculate(ResultCollection $collection, ResultCollection $groupedResults, ResultInterface $bound);

    /**
     * Name of principle
     *
     * @çeturn string
     */
    public function getName();
}