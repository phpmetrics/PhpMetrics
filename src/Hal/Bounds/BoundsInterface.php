<?php

/*
 * (c) Jean-François Lépine <https://twitter.com/Halleck45>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Hal\Bounds;
use Hal\Result\ResultCollection;
use Hal\Bounds\Result\Result;

/**
 * Bounds calculator
 *
 * @author Jean-François Lépine <https://twitter.com/Halleck45>
 */
interface BoundsInterface {

    /**
     * Calculate
     *
     * @param ResultCollection $collection
     * @return Result
     */
    public function calculate(ResultCollection $collection);
}