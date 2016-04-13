<?php
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

        // traverse hash to get dependencies and represents graph
        foreach($hash as $node) {
            foreach($node->getData()->getDependencies() as $dependencyName) {

                if($hash->has($dependencyName)) {
                    // dependencies is registered in hash
                    $adjacent = $hash->get($dependencyName);
                } else {
                    // dependency is not registered (example: external dependency from vendors)
                    $adjacent = new Node($dependencyName, new Klass($dependencyName));
                    $graph->insert($adjacent);
                }

                $node->addAdjacent($adjacent);
            }

            $graph->insert($node);
        }

        return $graph;
    }
}