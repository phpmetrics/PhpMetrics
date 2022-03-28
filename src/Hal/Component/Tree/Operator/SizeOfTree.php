<?php
declare(strict_types=1);

namespace Hal\Component\Tree\Operator;

use Hal\Component\Tree\Graph;
use Hal\Component\Tree\Node;
use Hal\Exception\GraphException\NoSizeForCyclicGraphException;
use function array_map;
use function array_sum;
use function max;
use function round;

/**
 * Calculates the size of a tree, in a graph. This is not possible to calculate the height of a graph when there is a
 * cyclic dependency on it.
 */
final class SizeOfTree
{
    public function __construct(
        private readonly Graph $graph
    ) {
        if ((new CycleDetector())->isCyclic($graph)) {
            throw NoSizeForCyclicGraphException::incalculableSize();
        }
    }

    /**
     * Get average height of graph.
     */
    public function getAverageHeightOfGraph(): float
    {
        $longestBranchesByRoot = array_map($this->getLongestBranch(...), $this->graph->getRootNodes());
        return round(array_sum($longestBranchesByRoot) / max(1, count($longestBranchesByRoot)), 2);
    }

    /**
     * Get the size of the longest branch starting from the given node.
     */
    private function getLongestBranch(Node $node): int
    {
        $max = 1;
        foreach ($node->getEdges() as $edge) {
            if ($node === $edge->getTo()) {
                continue;
            }
            $max = max($max, 1 + $this->getLongestBranch($edge->getTo()));
        }
        return $max;
    }
}
