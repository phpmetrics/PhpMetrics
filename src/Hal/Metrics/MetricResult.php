<?php

/*
 * (c) Jean-François Lépine <https://twitter.com/Halleck45>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Hal\Metrics;

/**
 * Represents a metric
 *
 * @author Jean-François Lépine <https://twitter.com/Halleck45>
 */
interface MetricResult {

    /**
     * @return array
     */
    public function asArray();
}
