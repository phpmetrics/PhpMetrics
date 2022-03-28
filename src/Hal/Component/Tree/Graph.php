<?php
declare(strict_types=1);

namespace Hal\Component\Tree;

use Hal\Exception\GraphException\NodeAlreadyDefinedException;
use Hal\Exception\GraphException\OriginNodeMissingException;
use Hal\Exception\GraphException\TargetNodeMissingException;
use Stringable;
use function array_key_exists;
use function sprintf;

/**
 * Represents a relational graph composed of nodes and edges.
 */
class Graph implements Stringable
{
    /** @var array<string, Node> */
    private array $data = [];
    /** @var array<Edge> */
    private array $edges = [];

    public function insert(Node $node): void
    {
        if ($this->has($node->getKey())) {
            throw NodeAlreadyDefinedException::inGraph($node);
        }
        $this->data[$node->getKey()] = $node;
    }

    public function addEdge(Node $from, Node $to): void
    {
        if (!$this->has($from->getKey())) {
            throw OriginNodeMissingException::inGraph($from);
        }
        if (!$this->has($to->getKey())) {
            throw TargetNodeMissingException::inGraph($to);
        }

        $edge = new Edge($from, $to);
        $from->addEdge($edge);
        $to->addEdge($edge);
        $this->edges[] = $edge;
    }

    /**
     * @return string
     */
    public function __toString(): string
    {
        $string = '';
        foreach ($this->data as $node) {
            $string .= sprintf("%s;\n", $node->getKey());
        }
        foreach ($this->edges as $edge) {
            $string .= sprintf("%s;\n", $edge);
        }
        return $string;
    }

    /**
     * @return array<int, Edge>
     */
    public function getEdges(): array
    {
        return $this->edges;
    }

    public function get(string $key): null|Node
    {
        return $this->has($key) ? $this->data[$key] : null;
    }

    /**
     * Returns the Node requested by $key by creating it if not already existing.
     *
     * @param string $key
     * @return Node
     */
    public function gather(string $key): Node
    {
        if (!$this->has($key)) {
            $this->insert(new Node($key));
        }
        return $this->data[$key];
    }

    public function has(string $key): bool
    {
        return array_key_exists($key, $this->data);
    }

    /**
     * @return array<string, Node>
     */
    public function all(): array
    {
        return $this->data;
    }

    public function resetVisits(): void
    {
        foreach ($this->data as $node) {
            $node->visited = false;
        }
    }

    /**
     * Get the list of all root nodes
     *
     * @return array<int, Node>
     */
    public function getRootNodes(): array
    {
        $roots = [];
        foreach ($this->all() as $node) {
            $isRoot = true;

            foreach ($node->getEdges() as $edge) {
                if ($edge->getFrom() !== $node) {
                    $isRoot = false;
                }
            }

            if ($isRoot) {
                $roots[] = $node;
            }
        }

        return $roots;
    }
}
