<?php

/*
 * (c) Jean-François Lépine <https://twitter.com/Halleck45>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Hal\Component\Tree;

use Hal\Component\Reflected\Klass;

class GraphFactory
{

    /**
     * @param HashMap $hash
     * @return Graph
     */
    public function factory(HashMap $hash)
    {
        $graph = new Graph();

        // insert all required nodes in graph
        foreach($hash as $node) {
            foreach($node->getData()->getDependencies() as $dependencyName) {
                if(!$hash->has($dependencyName)) {
                    // dependency is not registered (example: external dependency from vendors)
                    $adjacent = new Node($dependencyName, new Klass($dependencyName));
                    $graph->insert($adjacent);
                }
            }
            $graph->insert($node);
        }

        // relations
        foreach($hash as $from) {
            foreach($from->getData()->getDependencies() as $dependencyName) {
                $to = $graph->get($dependencyName);
                $graph->addEdge($from, $to);
            }
        }

        return $graph;
    }
}