<?php

/*
 * (c) Jean-François Lépine <https://twitter.com/Halleck45>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Hal\Component\Tree;

/**
 * @implements \IteratorAggregate<string,Node>
 */
class HashMap implements \Countable, \IteratorAggregate
{
    /**
     * @var array<string,Node>
     */
    private $nodes = [];

    /**
     * @param Node $node
     * @return static<Node>
     */
    public function attach(Node $node)
    {
        $this->nodes[$node->getKey()] = $node;
        return $this;
    }

    /**
     * @param string $key
     * @return Node|null
     */
    public function get($key)
    {
        return $this->has($key) ? $this->nodes[$key] : null;
    }

    /**
     * @param string $key
     * @return bool
     */
    public function has($key)
    {
        return isset($this->nodes[$key]);
    }

    /**
     * @return int
     */
    public function count()
    {
        return count($this->nodes);
    }

    public function getIterator()
    {
        return new \ArrayIterator($this->nodes);
    }
}
