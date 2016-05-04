<?php

/*
 * (c) Jean-François Lépine <https://twitter.com/Halleck45>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Hal\Metrics\Complexity\Structural\HenryAndKafura;

use Hal\Component\Tree\Node;
use Hal\Metrics\NodeMetric;

/**
 * Estimates coupling (based on work of Henry And Kafura)
 *
 * @author Jean-François Lépine <https://twitter.com/Halleck45>
 */
class Coupling implements NodeMetric {

    /**
     * Calculates coupling
     *
     * @param Node $node
     * @return Result
     */
    public function calculate(Node $node)
    {
        $efferent = $afferent = 0;
        foreach($node->getEdges() as $edge) {

            if($edge->getFrom()->getKey() == $node->getKey()) {
                // affects
                $afferent++;
            }

            if($edge->getTo()->getKey() == $node->getKey()) {
                // receive effects
                $efferent++;
            }
        }

        $result = new Result;
        $result
            ->setAfferentCoupling($afferent)
            ->setEfferentCoupling($efferent);

        if($efferent + $afferent > 0) {
            $result->setInstability($efferent / ($afferent + $efferent));
        }
        return $result;
    }
}