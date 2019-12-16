<?php

/*
 * (c) Jean-François Lépine <https://twitter.com/Halleck45>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Hal\Component\Tree;

class Node
{

    /**
     * @var mixed
     */
    private $data;

    /**
     * @var string
     */
    private $key;

    /**
     * @var Edge[]
     */
    private $edges = [];

    /**
     * @var bool
     */
    public $visited = false;

    /**
     * @var bool
     */
    public $cyclic = false;

    /**
     * @param string $key
     * @param mixed $data
     */
    public function __construct($key, $data = null)
    {
        $this->key = $key;
        $this->data = $data;
    }

    /**
     * @return string
     */
    public function getKey()
    {
        return $this->key;
    }

    /**
     * @return array
     */
    public function getAdjacents()
    {
        $adjacents = [];
        foreach ($this->edges as $edge) {
            if ($edge->getFrom()->getKey() != $this->getKey()) {
                $adjacents[$edge->getFrom()->getKey()] = $edge->getFrom();
            }
            if ($edge->getTo()->getKey() != $this->getKey()) {
                $adjacents[$edge->getTo()->getKey()] = $edge->getTo();
            }
        }
        return $adjacents;
    }

    /**
     * @return Edge[]
     */
    public function getEdges()
    {
        return $this->edges;
    }

    /**
     * @param Edge $edge
     * @return $this
     */
    public function addEdge(Edge $edge)
    {
        array_push($this->edges, $edge);
        return $this;
    }

    /**
     * @return mixed
     */
    public function getData()
    {
        return $this->data;
    }

    /**
     * @param mixed $data
     * @return Node
     */
    public function setData($data)
    {
        $this->data = $data;
        return $this;
    }

    /**
     * @return string Unique id for this node independent of class name or node type
     */
    public function getUniqueId()
    {
        return spl_object_hash($this);
    }
}
