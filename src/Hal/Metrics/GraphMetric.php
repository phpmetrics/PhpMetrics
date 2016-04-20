<?php

/*
 * (c) Jean-François Lépine <https://twitter.com/Halleck45>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Hal\Metrics;
use Hal\Component\Tree\Graph;

/**
 * @author Jean-François Lépine <https://twitter.com/Halleck45>
 */
interface GraphMetric {

    /**
     * @param Graph $graph
     * @return MetricResult
     * @internal param Node $node
     */
    public function calculate(Graph $graph);
}
