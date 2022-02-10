<?php

/*
 * (c) Jean-François Lépine <https://twitter.com/Halleck45>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Hal\Component\Tree;

class Graph implements \Countable
{
    /**
     * @var Node[]
     */
    private $data = [];

    /**
     * @var Edge[]
     */
    private $edges = [];

    /**
     * @param Node $node
     * @return $this
     */
    public function insert(Node $node)
    {
        if ($this->has($node->getKey())) {
            throw new GraphException(sprintf('node %s is already present', $node->getKey()));
        }
        $this->data[$node->getKey()] = $node;
        return $this;
    }

    /**
     * @param Node $from
     * @param Node $to
     * @return $this
     */
    public function addEdge(Node $from, Node $to)
    {
        if (!$this->has($from->getKey())) {
            throw new GraphException('from is not is in the graph');
        }
        if (!$this->has($to->getKey())) {
            throw new GraphException('to is not is in the graph');
        }

        $edge = new Edge($from, $to);
        $from->addEdge($edge);
        $to->addEdge($edge);
        array_push($this->edges, $edge);

        return $this;
    }

    /**
     * @return string
     */
    public function asString()
    {
        $string = '';
        foreach ($this->all() as $node) {
            $string .= sprintf("%s;\n", $node->getKey());
        }
        foreach ($this->getEdges() as $edge) {
            $string .= sprintf("%s;\n", $edge->asString());
        }
        return $string;
    }

    /**
     * @return Edge[]
     */
    public function getEdges()
    {
        return $this->edges;
    }

    /**
     * @param $key
     * @return Node|null
     */
    public function get($key)
    {
        return $this->has($key) ? $this->data[$key] : null;
    }

    /**
     * @param $key
     * @return bool
     */
    public function has($key)
    {
        return isset($this->data[$key]);
    }

    /**
     * @return int
     */
    #[\ReturnTypeWillChange]
    public function count()
    {
        return count($this->data);
    }

    /**
     * @return Node[]
     */
    public function all()
    {
        return $this->data;
    }

    /**
     * @return $this
     */
    public function resetVisits()
    {
        foreach ($this->all() as $node) {
            $node->visited = false;
        }
        return $this;
    }

    /**
     * Get the list of all root nodes
     *
     *      we can have array of roots : graph can be a "forest"
     *
     * @return array
     */
    public function getRootNodes()
    {
        $roots = [];
        foreach ($this->all() as $node) {
            $isRoot = true;

            foreach ($node->getEdges() as $edge) {
                if ($edge->getTo() == $node) {
                    $isRoot = false;
                }
            }

            if ($isRoot) {
                array_push($roots, $node);
            }
        }

        return $roots;
    }
}
