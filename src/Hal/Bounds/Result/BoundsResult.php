<?php

/*
 * (c) Jean-François Lépine <https://twitter.com/Halleck45>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Hal\Bounds\Result;
use Hal\Result\ExportableInterface;
use Hal\Result\ResultCollection;


/**
 * ResultBoundary
 *
 * @author Jean-François Lépine <https://twitter.com/Halleck45>
 */
class BoundsResult implements ExportableInterface, ResultInterface {

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
    public function __construct($min, $max, $average, $sum)
    {
       $this->min = $min;
       $this->max = $max;
       $this->average = $average;
       $this->sum = $sum;
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
     * @inheritdoc
     */
    public function getAverage($key) {
        return isset($this->average[$key]) ? $this->average[$key] : null;
    }

    /**
     * @inheritdoc
     */
    public function getMin($key) {
        return isset($this->min[$key]) ? $this->min[$key] : null;
    }

    /**
     * @inheritdoc
     */
    public function getMax($key) {
        return isset($this->max[$key]) ? $this->max[$key] : null;
    }

    /**
     * @inheritdoc
     */
    public function getSum($key) {
        return isset($this->sum[$key]) ? $this->sum[$key] : null;
    }

    /**
     * @inheritdoc
     */
    public function get($type, $key)
    {
        switch($type) {
            case 'max':
                return $this->getMax($key);
            case 'sum':
                return $this->getSum($key);
            case 'min':
                return $this->getMin($key);
            case 'average':
                return $this->getAverage($key);
        }
        return null;
    }
}