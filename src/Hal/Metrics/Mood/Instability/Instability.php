<?php

/*
 * (c) Jean-François Lépine <https://twitter.com/Halleck45>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Hal\Metrics\Mood\Instability;
use Hal\Component\Tree\Graph;
use Hal\Metrics\Complexity\Structural\HenryAndKafura\Coupling;
use Hal\Metrics\GraphMetric;


/**
 * Estimates Instability of package
 *
 * @author Jean-François Lépine <https://twitter.com/Halleck45>
 */
class Instability implements GraphMetric {

    /**
     * Calculate instability
     *
     * @param Graph $graph
     * @return Result
     */
    public function calculate(Graph $graph) {

        $ca = $ce = $i = 0;

        $coupling = new Coupling();
        foreach($graph->all() as $node) {

            $r = $coupling->calculate($node);
            $ce += $r->getEfferentCoupling();
            $ca += $r->getAfferentCoupling();
        }

        $result = new Result;
        if($ca + $ce > 0) {
            $i = round($ce / ($ca + $ce), 2);
        }
        $result->setInstability($i);
        return $result;
    }

}
