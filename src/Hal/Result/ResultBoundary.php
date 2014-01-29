<?php

/*
 * (c) Jean-François Lépine <https://twitter.com/Halleck45>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Hal\Result;


/**
 * ResultBoundary
 *
 * @author Jean-François Lépine <https://twitter.com/Halleck45>
 */
class ResultBoundary implements ExportableInterface {

    /**
     * Resultset collection
     *
     * @var ResultCollection
     */
    private $collection;

    /**
     * Average values
     *
     * @var array
     */
    private $average = array();

    /**
     * Min values
     *
     * @var array
     */
    private $min = array();

    /**
     * Max values
     *
     * @var array
     */
    private $max = array();

    /**
     * Constructor
     *
     * @param ResultCollection $collection
     */
    public function __construct(ResultCollection $collection)
    {
        $this->collection = $collection;

        $array = $collection->asArray();
        $avg = array();
        foreach($array as $values) {
            foreach($values as $k => $v) {
                if(!isset($avg[$k])) {
                    $avg[$k] = array();
                    $this->min[$k] = $this->max[$k] = $v;
                }
                array_push($avg[$k], (float) $v);
                if($v < $this->min[$k]) {
                    $this->min[$k] = $v;
                }
                if($v > $this->max[$k]) {
                    $this->max[$k] = $v;
                }
            }
        }
        foreach($avg as $key => $values) {
            $this->average[$key] = array_sum($values) / count($values, COUNT_NORMAL);
        }
    }

    /**
     * @inheritdoc
     */
    public function asArray() {
        return array(
            'average' => $this->average
            , 'min' => $this->min
            , 'max' => $this->max
        );
    }

    /**
     * Get average for
     *
     * @param $key
     * @return null
     */
    public function getAverage($key) {
        return isset($this->average[$key]) ? $this->average[$key] : null;
    }

    /**
     * Get min for
     *
     * @param $key
     * @return null
     */
    public function getMin($key) {
        return isset($this->min[$key]) ? $this->min[$key] : null;
    }

    /**
     * Get max for
     *
     * @param $key
     * @return null
     */
    public function getMax($key) {
        return isset($this->max[$key]) ? $this->max[$key] : null;
    }
}