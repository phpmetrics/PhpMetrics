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
     * Sums
     *
     * @var array
     */
    private $sum = array();

    /**
     * Constructor
     *
     * @param ResultCollection $collection
     */
    public function __construct(ResultCollection $collection)
    {
        $this->collection = $collection;
        $array = $collection->asArray();

        $arrayMerged = call_user_func_array('array_merge_recursive', $array);

        foreach($arrayMerged as $key => $values) {
            $this->max[$key] = max($values);
            $this->min[$key] = min($values);
            $this->sum[$key] = array_sum($values);
            $this->average[$key] = $this->sum[$key] / count($values, COUNT_NORMAL);
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

    /**
     * Get sum for
     *
     * @param $key
     * @return null
     */
    public function getSum($key) {
        return isset($this->sum[$key]) ? $this->sum[$key] : null;
    }
}