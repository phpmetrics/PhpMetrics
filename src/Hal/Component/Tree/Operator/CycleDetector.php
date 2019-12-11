<?php

/*
 * (c) Jean-François Lépine <https://twitter.com/Halleck45>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Hal\Component\Tree\Operator;

use Hal\Component\Tree\Graph;
use Hal\Component\Tree\Node;

/**
 * @package Hal\Component\Tree\Util
 * @see http://www.geeksforgeeks.org/detect-cycle-in-a-graph/
 */
class CycleDetector
{
    /**
     * Check if graph contains cycle
     *
     * Each node in cycle is flagged with the "cyclic" attribute
     *
     * @param Graph $graph
     * @return bool
     */
    public function isCyclic(Graph $graph)
    {
        // prepare stack
        $recursionStack = [];
        foreach ($graph->all() as $node) {
            $recursionStack[$node->getKey()] = false;
        }

        // start analysis
        $isCyclic = false;
        foreach ($graph->getEdges() as $edge) {
            if ($r = $this->detectCycle($edge->getFrom(), $recursionStack)) {
                $edge->cyclic = true;
                $isCyclic = true;
            }

            $recursionStack[$node->getKey()] = false;
        }

        $graph->resetVisits();

        return $isCyclic;
    }

    /**
     * @param Node $node
     * @param $recursionStack
     * @return bool
     */
    private function detectCycle(Node $node, &$recursionStack)
    {
        if (!$node->visited) {
            // mark the current node as visited and part of recursion stack
            $recursionStack[$node->getKey()] = true;
            $node->visited = true;

            // recur for all the vertices adjacent to this vertex
            foreach ($node->getEdges() as $edge) {
                if ($edge->getTo() === $node) {
                    continue;
                }

                if (!$edge->getTo()->visited && $this->detectCycle($edge->getTo(), $recursionStack)) {
                    $edge->cyclic = $edge->getTo()->cyclic = true;
                    return true;
                } elseif ($recursionStack[$edge->getTo()->getKey()]) {
                    $edge->cyclic = $edge->getTo()->cyclic = true;
                    return true;
                }
            }
        }
        // remove the vertex from recursion stack
        $recursionStack[$node->getKey()] = false;
        return false;
    }
}
