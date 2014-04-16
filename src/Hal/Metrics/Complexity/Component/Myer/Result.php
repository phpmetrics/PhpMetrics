<?php

/*
 * (c) Jean-François Lépine <https://twitter.com/Halleck45>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Hal\Metrics\Complexity\Component\Myer;
use Hal\Component\Result\ExportableInterface;

/**
 * Representation of McCaybe measure
 *
 * @author Jean-François Lépine <https://twitter.com/Halleck45>
 */
class Result implements ExportableInterface {

    /**
     * Cyclomatic result
     *
     * @var \Hal\Metrics\Complexity\Component\McCabe\Result
     */
    private $mcCabe;

    /**
     * Number of operators
     * @var int
     */
    private $numberOfOperators = 0;

    /**
     * @inheritdoc
     */
    public function asArray() {
        return array (
            'myerInterval' => $this->getInterval()
            , 'myerDistance' => $this->getDistance()
            , 'cyclomaticComplexity' => $this->getCyclomaticComplexityNumber()
            , 'operators' => $this->getNumberOfOperators()
        );
    }

    /**
     * Get distance (absolute interval) in Myer's interval
     *
     * @return number
     */
    public function getDistance() {
        return abs($this->getCyclomaticComplexityNumber() - ($this->getCyclomaticComplexityNumber() + $this->getNumberOfOperators()));
    }

    /**
     * Get Myer's interval
     *
     * @return string
     */
    public function getInterval() {
        return sprintf('%s:%s', $this->getCyclomaticComplexityNumber(), $this->getCyclomaticComplexityNumber() + $this->getNumberOfOperators());
    }

    /**
     * get the cyclomatic complexity number
     *
     * @return int
     */
    public function getCyclomaticComplexityNumber() {
        return !is_null($this->mcCabe) ? $this->mcCabe->getCyclomaticComplexityNumber() : 0;
    }

    /**
     * Set McCabe result
     *
     * @param \Hal\Metrics\Complexity\Component\McCabe\Result $mcCabe
     * @return $this
     */
    public function setMcCabe(\Hal\Metrics\Complexity\Component\McCabe\Result $mcCabe)
    {
        $this->mcCabe = $mcCabe;
        return $this;
    }

    /**
     * Set number of operators
     *
     * @param $numberOfOperators
     * @return $this
     */
    public function setNumberOfOperators($numberOfOperators)
    {
        $this->numberOfOperators = $numberOfOperators;
        return $this;
    }

    /**
     * Get number of operators
     *
     * @return int
     */
    public function getNumberOfOperators()
    {
        return $this->numberOfOperators;
    }
}