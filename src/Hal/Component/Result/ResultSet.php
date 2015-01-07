<?php

/*
 * (c) Jean-François Lépine <https://twitter.com/Halleck45>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Hal\Component\Result;
use Hal\Component\OOP\Extractor\Result;


/**
 * ResultSet
 *
 * @author Jean-François Lépine <https://twitter.com/Halleck45>
 */
class ResultSet implements ExportableInterface, ResultSetInterface {

    /**
     * Filename
     *
     * @var string
     */
    private $filename;

    /**
     * Halstead Result
     * @var \Hal\Metrics\Complexity\Text\Halstead\Result
     */
    private $halstead;

    /**
     * LOC Result
     *
     * @var \Hal\Metrics\Complexity\Text\Length\Result
     */
    private $loc;

    /**
     * McCabe Result
     *
     * @var \Hal\Metrics\Complexity\Component\McCabe\Result
     */
    private $mcCabe;

    /**
     * Myer's interval result
     *
     * @var \Hal\Metrics\Complexity\Component\Myer\Result
     */
    private $myer;

    /**
     * Maintainability Result
     *
     * @var \Hal\Metrics\Design\Component\MaintenabilityIndex\Result
     */
    private $maintenabilityIndex;

    /**
     * Coupling
     *
     * @var \Hal\Metrics\Complexity\Structural\HenryAndKafura\Result
     */
    private $coupling;

    /**
     * Infos about OOP
     *
     * @var Result
     */
    private $oop;

    /**
     * Lack of cohesion of methods
     *
     * @var
     */
    private $lcom;

    /**
     * System complexity result
     *
     * @var \Hal\Metrics\Complexity\Structural\CardAndAgresti\Result
     */
    private $systemComplexity;

    /**
     * Constructor
     *
     * @param string $filename
     */
    public function __construct($filename) {
        $this->filename = (string) $filename;
    }

    /**
     * @inheritdoc
     */
    public function getName() {
        return $this->filename;
    }

    /**
     * @inheritdoc
     */
    public function asArray() {
        return array_merge(
            array(
                'filename' => $this->getFilename()
                , 'name' => $this->getName()
            )
            , $this->getLoc() ? $this->getLoc()->asArray() : array()
            , $this->getHalstead() ? $this->getHalstead()->asArray() : array()
            , $this->getMaintenabilityIndex() ? $this->getMaintenabilityIndex()->asArray() : array()
            , $this->getCoupling() ? $this->getCoupling()->asArray() : array()
            , $this->getOop() ? $this->getOop()->asArray() : array()
            , $this->getMcCabe() ? $this->getMcCabe()->asArray() : array()
            , $this->getMyer() ? $this->getMyer()->asArray() : array()
            , $this->getLcom() ? $this->getLcom()->asArray() : array()
            , $this->getSystemComplexity() ? $this->getSystemComplexity()->asArray() : array()
        );
    }

    /**
     * @param \Hal\Metrics\Complexity\Text\Halstead\Result $halstead
     * @return $this
     */
    public function setHalstead(\Hal\Metrics\Complexity\Text\Halstead\Result $halstead)
    {
        $this->halstead = $halstead;
        return $this;
    }

    /**
     * @return \Hal\Metrics\Complexity\Text\Halstead\Result
     */
    public function getHalstead()
    {
        return $this->halstead;
    }

    /**
     * @param \Hal\Metrics\Complexity\Text\Length\Result $loc
     * @return $this
     */
    public function setLoc(\Hal\Metrics\Complexity\Text\Length\Result $loc)
    {
        $this->loc = $loc;
        return $this;
    }

    /**
     * @return \Hal\Metrics\Complexity\Text\Length\Result
     */
    public function getLoc()
    {
        return $this->loc;
    }


    /**
     * @param \Hal\Metrics\Complexity\Component\McCabe\Result $mcCabe
     * @return $this
     */
    public function setMcCabe(\Hal\Metrics\Complexity\Component\McCabe\Result $mcCabe)
    {
        $this->mcCabe = $mcCabe;
        return $this;
    }

    /**
     * @return \Hal\Metrics\Complexity\Component\McCabe\Result
     */
    public function getMcCabe()
    {
        return $this->mcCabe;
    }

    /**
     * @param \Hal\Metrics\Complexity\Component\Myer\Result $myer
     * @return $this
     */
    public function setMyer($myer)
    {
        $this->myer = $myer;
        return $this;
    }

    /**
     * @return \Hal\Metrics\Complexity\Component\Myer\Result
     */
    public function getMyer()
    {
        return $this->myer;
    }

    /**
     * @param \Hal\Metrics\Design\Component\MaintenabilityIndex\Result $maintenabilityIndex
     * @return $this
     */
    public function setMaintenabilityIndex(\Hal\Metrics\Design\Component\MaintenabilityIndex\Result $maintenabilityIndex)
    {
        $this->maintenabilityIndex = $maintenabilityIndex;
        return $this;
    }

    /**
     * @return \Hal\Metrics\Design\Component\MaintenabilityIndex\Result
     */
    public function getMaintenabilityIndex()
    {
        return $this->maintenabilityIndex;
    }

    /**
     * Get filename associated to the result set
     *
     * @return string
     */
    public function getFilename()
    {
        return $this->filename;
    }

    /**
     * @param \Hal\Metrics\Complexity\Structural\HenryAndKafura\Result $coupling
     * @return $this
     */
    public function setCoupling($coupling)
    {
        $this->coupling = $coupling;
        return $this;
    }

    /**
     * @return \Hal\Metrics\Complexity\Structural\HenryAndKafura\Result
     */
    public function getCoupling()
    {
        return $this->coupling;
    }

    /**
     * @param \Hal\Component\OOP\Extractor\Result $oop
     * @return $this
     */
    public function setOop($oop)
    {
        $this->oop = $oop;
        return $this;
    }

    /**
     * @return \Hal\Component\OOP\Extractor\Result
     */
    public function getOop()
    {
        return $this->oop;
    }

    /**
     * @param \Hal\Metrics\Complexity\Structural\LCOM\Result $lcom
     */
    public function setLcom(\Hal\Metrics\Complexity\Structural\LCOM\Result $lcom)
    {
        $this->lcom = $lcom;
    }

    /**
     * @return \Hal\Metrics\Complexity\Structural\LCOM\Result
     */
    public function getLcom()
    {
        return $this->lcom;
    }

    /**
     * @param \Hal\Metrics\Complexity\Structural\CardAndAgresti\Result $systemComplexity
     * @return $this
     */
    public function setSystemComplexity(\Hal\Metrics\Complexity\Structural\CardAndAgresti\Result $systemComplexity)
    {
        $this->systemComplexity = $systemComplexity;
        return $this;
    }

    /**
     * @return \Hal\Metrics\Complexity\Structural\CardAndAgresti\Result
     */
    public function getSystemComplexity()
    {
        return $this->systemComplexity;
    }



}