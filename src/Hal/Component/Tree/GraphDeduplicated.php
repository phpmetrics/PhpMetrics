<?php
declare(strict_types=1);

namespace Hal\Component\Tree;

/**
 * Deduplicated version of Graph object, linking nodes via unique named edges. Using this specific Graph, 2 nodes will
 * not be able to be linked by the same edge more than once.
 */
final class GraphDeduplicated extends Graph
{
    /** @var array<string, bool> List of already present edges in this graph. */
    private array $edgesMap = [];

    public function addEdge(Node $from, Node $to): void
    {
        $key = $from->getUniqueId() . '➔' . $to->getUniqueId();

        if (isset($this->edgesMap[$key])) {
            return;
        }

        $this->edgesMap[$key] = true;
        parent::addEdge($from, $to);
    }
}
