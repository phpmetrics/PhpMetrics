<?php
declare(strict_types=1);

namespace Hal\Component\Tree\Operator;

use Hal\Component\Tree\Graph;
use Hal\Component\Tree\Node;
use function array_fill_keys;
use function array_map;

/**
 * @see http://www.geeksforgeeks.org/detect-cycle-in-a-graph/
 */
final class CycleDetector
{
    /**
     * Check if graph contains cycle.
     * Each node in cycle is flagged with the "cyclic" attribute
     */
    public function isCyclic(Graph $graph): bool
    {
        // Prepare stack.
        $recursionStack = array_fill_keys(
            array_map(static fn (Node $node): string => $node->getKey(), $graph->all()),
            false
        );

        // Start analysis.
        $isCyclic = false;
        foreach ($graph->getEdges() as $edge) {
            if ($this->detectCycle($edge->getFrom(), $recursionStack)) {
                $isCyclic = $edge->cyclic = true;
            }
        }
        $graph->resetVisits();
        return $isCyclic;
    }

    /**
     * Detects if the given Node has cyclic relations with at least another Node.
     *
     * @param array<string, bool> $recursionStack
     */
    private function detectCycle(Node $node, array &$recursionStack): bool
    {
        if (!$node->visited) {
            // Mark the current node as visited and part of recursion stack.
            $recursionStack[$node->getKey()] = true;
            $node->visited = true;

            // Recur for all the vertices adjacent to this vertex.
            foreach ($node->getEdges() as $edge) {
                $to = $edge->getTo();
                // Ignore self-referencing relations.
                if ($to === $node) {
                    continue;
                }

                if (
                    // Next node not yet visited and when visited, a cycle is detected...
                    (!$to->visited && $this->detectCycle($to, $recursionStack))
                    //... or recursion is found, demonstrating a cyclic relation.
                    || ($recursionStack[$to->getKey()])
                ) {
                    $edge->cyclic = $to->cyclic = true;
                    return true;
                }
            }
        }

        // Remove the vertex from recursion stack.
        $recursionStack[$node->getKey()] = false;
        return false;
    }
}
