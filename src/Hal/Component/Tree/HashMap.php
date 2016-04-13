<?php
namespace Hal\Component\Tree;


class HashMap implements \Countable, \IteratorAggregate
{
    /**
     * @var array
     */
    private $nodes = array();

    /**
     * @param Node $node
     * @return $this
     */
    public function attach(Node $node)
    {
        $this->nodes[$node->getKey()] = $node;
        return $this;
    }

    /**
     * @param $key
     * @return null
     */
    public function get($key)
    {
        return $this->has($key) ? $this->nodes[$key] : null;
    }

    /**
     * @param $key
     * @return bool
     */
    public function has($key)
    {
        return isset($this->nodes[$key]);
    }

    /**
     * @return int
     */
    public function count() {
        return sizeof($this->nodes);
    }

    /**
     * @return \ArrayIterator
     */
    public function getIterator() {
        return new \ArrayIterator($this->nodes);
    }
}