<?php

/*
 * (c) Jean-François Lépine <https://twitter.com/Halleck45>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Hal\Result;
use Traversable;


/**
 * ResultSet
 *
 * @author Jean-François Lépine <https://twitter.com/Halleck45>
 */
class ResultCollection implements ExportableInterface, \IteratorAggregate, \ArrayAccess {

    /**
     * Results
     *
     * @var array
     */
    private $results = array();

    /**
     * Push resultset
     *
     * @param ResultSet $resultset
     * @return $this
     */
    public function push(ResultSet $resultset) {
        array_push($this->results, $resultset);
        return $this;
    }

    /**
     * @inheritedDoc
     */
    public function asArray() {
        $array = array();
        foreach($this->results as $result) {
            array_push($array, $result->asArray());
        }
        return $array;
    }

    /**
     * @inheritedDoc
     */
    public function getIterator()
    {
        return $this->results;
    }

    /**
     * @inheritedDoc
     */
    public function offsetExists($offset)
    {
        return isset($this->results[$offset]);
    }

    /**
     * @inheritedDoc
     */
    public function offsetGet($offset)
    {
        return $this->results[$offset];
    }

    /**
     * @inheritedDoc
     */
    public function offsetSet($offset, $value)
    {
        $this->results[$offset] = $value;
    }

    /**
     * @inheritedDoc
     */
    public function offsetUnset($offset)
    {
        unset($this->results[$offset]);
    }
}