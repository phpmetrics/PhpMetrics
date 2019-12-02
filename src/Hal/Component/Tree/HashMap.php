<?php

/*
 * (c) Jean-François Lépine <https://twitter.com/Halleck45>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Hal\Component\Tree;

class HashMap implements \Countable, \IteratorAggregate
{
    /**
     * @var array
     */
    private $nodes = [];

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
     * @return Node
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
    public function count()
    {
        return count($this->nodes);
    }

    /**
     * @return \ArrayIterator
     */
    public function getIterator()
    {
        return new \ArrayIterator($this->nodes);
    }
}
