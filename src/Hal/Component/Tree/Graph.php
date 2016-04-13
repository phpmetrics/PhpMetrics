<?php
namespace Hal\Component\Tree;

class Graph implements \Countable {

    /**
     * @var array
     */
    private $datas = array();

    /**
     * @param Node $node
     * @return $this
     */
    public function insert(Node $node)
    {
        $this->datas[$node->getKey()] = $node;
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
}