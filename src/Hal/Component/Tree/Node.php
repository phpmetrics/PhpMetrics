<?php
declare(strict_types=1);

namespace Hal\Component\Tree;

use Stringable;
use function spl_object_hash;

/**
 * Represents a node in a graph, that can be linked to other nodes via edges.
 */
final class Node implements Stringable
{
    /** @var array<int, Edge> */
    private array $edges = [];
    public bool $visited = false;
    public bool $cyclic = false;

    public function __construct(
        private readonly string $key,
        private mixed $data = null
    ) {
    }

    public function getKey(): string
    {
        return $this->key;
    }

    /**
     * Get all nodes connected to the current Node.
     *
     * @return array<string, Node>
     */
    public function getAllNextBy(): array
    {
        $nextBy = [];
        foreach ($this->edges as $edge) {
            [$from, $to] = [$edge->getFrom(), $edge->getTo()];
            if ($from->getKey() !== $this->getKey()) {
                $nextBy[$from->getKey()] = $from;
            }
            if ($to->getKey() !== $this->getKey()) {
                $nextBy[$to->getKey()] = $to;
            }
        }
        return $nextBy;
    }

    /**
     * @return array<int, Edge>
     */
    public function getEdges(): array
    {
        return $this->edges;
    }

    public function addEdge(Edge $edge): void
    {
        $this->edges[] = $edge;
    }

    public function getData(): mixed
    {
        return $this->data;
    }

    public function setData(mixed $data): void
    {
        $this->data = $data;
    }

    /**
     * Returns a unique id for this node independent of class name or node type.
     */
    public function getUniqueId(): string
    {
        return spl_object_hash($this);
    }

    public function __toString(): string
    {
        return $this->key;
    }
}
