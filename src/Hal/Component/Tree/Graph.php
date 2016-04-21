<?php
namespace Hal\Component\Tree;

class Graph implements \Countable {

    /**
     * @var array
     */
    private $datas = array();

    /**
     * @var Edge[]
     */
    private $edges = array();

    /**
     * @param Node $node
     * @return $this
     */
    public function insert(Node $node)
    {
        if($this->has($node->getKey())) {
            throw new GraphException(sprintf('node %s is already present', $node->getKey()));
        }
        $this->datas[$node->getKey()] = $node;
        return $this;
    }

    /**
     * @param Node $from
     * @param Node $to
     * @return $this
     */
    public function addEdge(Node $from, Node $to)
    {
        if(!$this->has($from->getKey())) {
            throw new GraphException('from is not is in the graph');
        }
        if(!$this->has($to->getKey())) {
            throw new GraphException('to is not is in the graph');
        }

        $edge = new Edge($from, $to);
        $from->addEdge($edge);
        $to->addEdge($edge);
        array_push($this->edges, $edge);
        return $this;
    }

    /**
     * @param $key
     * @return null
     */
    public function get($key)
    {
        return $this->has($key) ? $this->datas[$key] : null;
    }

    /**
     * @param $key
     * @return bool
     */
    public function has($key)
    {
        return isset($this->datas[$key]);
    }

    /**
     * @return int
     */
    public function count()
    {
        return sizeof($this->datas);
    }

    /**
     * @return Node[]
     */
    public function all()
    {
        return $this->datas;
    }
}