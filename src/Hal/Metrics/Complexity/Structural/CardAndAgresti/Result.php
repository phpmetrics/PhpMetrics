<?php

/*
 * (c) Jean-François Lépine <https://twitter.com/Halleck45>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Hal\Metrics\Complexity\Structural\CardAndAgresti;
use Hal\Component\Result\ExportableInterface;

/**
 * Representation of McCaybe measure
 *
 * @author Jean-François Lépine <https://twitter.com/Halleck45>
 */
class Result implements ExportableInterface {



    /**
     * Relative system complexity
     *
     *      Relative system complexity RSYSC = average(SC + DC) over all procedures
     *
     * @var integer
     */
    private $relativeSystemComplexity = 0;

    /**
     * Total Data complexity (internal)
     *
     *      Total system complexity SYSC = sum(SC + DC) over all procedures
     *
     * @var integer
     */
    private $totalSystemComplexity = 0;

    /**
     * Relative Data complexity (internal)
     *
     * @var integer
     */
    private $relativeDataComplexity = 0;

    /**
     * Relative structural complexity (external)
     *
     * @var integer
     */
    private $relativeStructuralComplexity = 0;

    /**
     * Total Data complexity (internal)
     *
     * @var integer
     */
    private $totalDataComplexity = 0;

    /**
     * Total structural complexity (external)
     *
     * @var integer
     */
    private $totalStructuralComplexity = 0;

    /**
     * @inheritdoc
     */
    public function asArray() {
        return array(
            'sysc' => $this->getTotalSystemComplexity()
            , 'rsysc' => $this->getRelativeSystemComplexity()
            , 'dc' => $this->getTotalDataComplexity()
            , 'rdc' => $this->getRelativeDataComplexity()
            , 'sc' => $this->getTotalStructuralComplexity()
            , 'rsc' => $this->getRelativeStructuralComplexity()
        );
    }

    /**
     * @param int $relativeSystemComplexity
     * @return $this
     */
    public function setRelativeSystemComplexity($relativeSystemComplexity)
    {
        $this->relativeSystemComplexity = $relativeSystemComplexity;
        return $this;
    }

    /**
     * @return int
     */
    public function getRelativeSystemComplexity()
    {
        return $this->relativeSystemComplexity;
    }

    /**
     * @param int $totalSystemComplexity
     * @return $this
     */
    public function setTotalSystemComplexity($totalSystemComplexity)
    {
        $this->totalSystemComplexity = $totalSystemComplexity;
        return $this;
    }

    /**
     * @return int
     */
    public function getTotalSystemComplexity()
    {
        return $this->totalSystemComplexity;
    }

    /**
     * @param float $dataComplexity
     * @return $this
     */
    public function setDataComplexity($dataComplexity)
    {
        $this->dataComplexity = $dataComplexity;
        return $this;;
    }

    /**
     * @param int $relativeDataComplexity
     * @return $this
     */
    public function setRelativeDataComplexity($relativeDataComplexity)
    {
        $this->relativeDataComplexity = $relativeDataComplexity;
        return $this;
    }

    /**
     * @return int
     */
    public function getRelativeDataComplexity()
    {
        return $this->relativeDataComplexity;
    }

    /**
     * @param int $relativeStructuralComplexity
     * @return $this
     */
    public function setRelativeStructuralComplexity($relativeStructuralComplexity)
    {
        $this->relativeStructuralComplexity = $relativeStructuralComplexity;
        return $this;
    }

    /**
     * @return int
     */
    public function getRelativeStructuralComplexity()
    {
        return $this->relativeStructuralComplexity;
    }

    /**
     * @param int $totalDataComplexity
     * @return $this
     */
    public function setTotalDataComplexity($totalDataComplexity)
    {
        $this->totalDataComplexity = $totalDataComplexity;
        return $this;
    }

    /**
     * @return int
     */
    public function getTotalDataComplexity()
    {
        return $this->totalDataComplexity;
    }

    /**
     * @param int $totalStructuralComplexity
     * @return $this
     */
    public function setTotalStructuralComplexity($totalStructuralComplexity)
    {
        $this->totalStructuralComplexity = $totalStructuralComplexity;
        return $this;
    }

    /**
     * @return int
     */
    public function getTotalStructuralComplexity()
    {
        return $this->totalStructuralComplexity;
    }





}