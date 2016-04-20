<?php

/*
 * (c) Jean-François Lépine <https://twitter.com/Halleck45>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Hal\Metrics\Mood\Abstractness;
use Hal\Component\Tree\Graph;
use Hal\Metrics\GraphMetric;


/**
 * Estimates Abstractness of package
 *
 * @author Jean-François Lépine <https://twitter.com/Halleck45>
 */
class Abstractness implements GraphMetric {

    /**
     * Calculate abstractness
     *
     * @param Graph $graph
     * @return Result
     */
    public function calculate(Graph $graph) {

        $ac = $cc = $abstractness = 0;

        foreach($graph->all() as $node) {
            $class = $node->getData();
            if (($class->isAbstract() ||$class->isInterface())) {
                $ac++;
            } else {
                $cc++;
            }
        }

        $result = new Result;
        if($ac + $cc > 0) {
            $abstractness = round($ac / ($ac + $cc), 2);
        }
        $result->setAbstractness($abstractness);
        return $result;
    }

};
