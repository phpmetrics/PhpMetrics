<?php

/*
 * (c) Jean-François Lépine <https://twitter.com/Halleck45>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace MaintenabilityIndex;


/**
 * Representation of Maintenability Index
 *
 * @author Jean-François Lépine <https://twitter.com/Halleck45>
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