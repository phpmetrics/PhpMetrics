<?php

/*
 * (c) Jean-François Lépine <https://twitter.com/Halleck45>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Hal\Result;


/**
 * ResultSet
 *
 * @author Jean-François Lépine <https://twitter.com/Halleck45>
 */
class ResultSet implements ExportableInterface {

    /**
     * Filename
     *
     * @var string
     */
    private $filename;

    /**
     * Halstead Result
     * @var \Hal\Halstead\Result
     */
    private $halstead;

    /**
     * LOC Result
     *
     * @var \Hal\Loc\Result
     */
    private $loc;

    /**
     * Maintenability Result
     *
     * @var \Hal\MaintenabilityIndex\Result
     */
    private $maintenabilityIndex;

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
    public function asArray() {
        return array_merge(
            array('filename' => $this->getFilename())
            , $this->getHalstead()->asArray()
            , $this->getLoc()->asArray()
            , $this->getMaintenabilityIndex()->asArray()
        );
    }

    /**
     * @param \Hal\Halstead\Result $halstead
     */
    public function setHalstead(\Hal\Halstead\Result $halstead)
    {
        $this->halstead = $halstead;
        return $this;
    }

    /**
     * @return \Hal\Halstead\Result
     */
    public function getHalstead()
    {
        return $this->halstead;
    }

    /**
     * @param \Hal\Loc\Result $loc
     */
    public function setLoc(\Hal\Loc\Result $loc)
    {
        $this->loc = $loc;
        return $this;
    }

    /**
     * @return \Hal\Loc\Result
     */
    public function getLoc()
    {
        return $this->loc;
    }

    /**
     * @param \Hal\MaintenabilityIndex\Result $maintenabilityIndex
     */
    public function setMaintenabilityIndex(\Hal\MaintenabilityIndex\Result $maintenabilityIndex)
    {
        $this->maintenabilityIndex = $maintenabilityIndex;
        return $this;
    }

    /**
     * @return \Hal\MaintenabilityIndex\Result
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
}