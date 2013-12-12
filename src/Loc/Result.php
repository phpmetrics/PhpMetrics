<?php
namespace Loc;

/**
 * Class FileInfo
 * @package Loc
 */
class Result {

    /**
     * Lines of code
     *
     * @var integer
     */
    private $loc;

    /**
     * Logical Lines of code
     *
     * @var integer
     */
    private $logicalLoc;

    /**
     * Complexity cyclomatic
     *
     * @var integer
     */
    private $complexityCyclomatic;

    /**
     * @param int $complexityCyclomatic
     */
    public function setComplexityCyclomatic($complexityCyclomatic)
    {
        $this->complexityCyclomatic = $complexityCyclomatic;
        return $this;
    }

    /**
     * @return int
     */
    public function getComplexityCyclomatic()
    {
        return $this->complexityCyclomatic;
    }

    /**
     * @param int $loc
     */
    public function setLoc($loc)
    {
        $this->loc = $loc;
        return $this;
    }

    /**
     * @return int
     */
    public function getLoc()
    {
        return $this->loc;
    }

    /**
     * @param int $logicalLoc
     */
    public function setLogicalLoc($logicalLoc)
    {
        $this->logicalLoc = $logicalLoc;
        return $this;
    }

    /**
     * @return int
     */
    public function getLogicalLoc()
    {
        return $this->logicalLoc;
    }




}