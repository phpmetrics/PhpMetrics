<?php
namespace MaintenabilityIndex;

/**
 * Class Result
 * @package MaintenabilityIndex
 */
class Result {

    /**
     * Maintenability index
     * Designed in 1991 by Paul Oman and Jack Hagemeister at the University of Idaho
     *
     * @var float
     */
    private $maintenabilityIndex;

    /**
     * @param float $maintenabilityIndex
     */
    public function setMaintenabilityIndex($maintenabilityIndex)
    {
        $this->maintenabilityIndex = $maintenabilityIndex;
    }

    /**
     * @return float
     */
    public function getMaintenabilityIndex()
    {
        return $this->maintenabilityIndex;
    }


}