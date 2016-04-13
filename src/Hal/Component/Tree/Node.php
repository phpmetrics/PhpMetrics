<?php
namespace Hal\Component\Tree;

class Node {

    /**
     * @var mixed
     */
    private $data;

    /**
     * @var string
     */
    private $key;

    /**
     * @var array
     */
    private $adjacents = array();

    /**
     * @var bool
     */
    public $visited = false;

    /**
     * Node constructor.
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
        return $this->adjacents;
    }

    /**
     * @param Node $adjacent
     * @return $this
     */
    public function addAdjacent(Node $adjacent)
    {
        array_push($this->adjacents, $adjacent);
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

}