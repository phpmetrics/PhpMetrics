<?php

/*
 * (c) Jean-François Lépine <https://twitter.com/Halleck45>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Hal\Component\Aggregator;
use Hal\Component\Result\ResultCollection;

/**
 * Agregates by directory
 *
 * @author Jean-François Lépine <https://twitter.com/Halleck45>
 */
interface Aggregator {


    /**
     * Aggregates results by group
     *
     * @param ResultCollection $results
     * @return ResultCollection[]
     */
    public function aggregates(ResultCollection $results);
}