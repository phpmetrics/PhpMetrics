<?php

/*
 * (c) Jean-François Lépine <https://twitter.com/Halleck45>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Hal\MaintenabilityIndex;
use Hal\Result\ExportableInterface;


/**
 * Representation of Maintenability Index
 *
 * @author Jean-François Lépine <https://twitter.com/Halleck45>
 */
class Result implements ExportableInterface {

    /**
     * Maintenability index
     * Designed in 1991 by Paul Oman and Jack Hagemeister at the University of Idaho
     *
     * @var float
     */
    private $maintenabilityIndex;


    /**
     * Weight of comment
     * perCM = cpercent of comment lines
     * MIcw = 50 * sin(sqrt(2.4 * perCM))
     *
     * @var int
     */
    private $commentWeight = 0;

    /**
     * @inheritdoc
     */
    public function asArray() {
        return array(
            'maintenabilityIndex' => (string) $this->getMaintenabilityIndex()
            , 'commentWeight' => (float) $this->getCommentWeight()
        );
    }

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

    /**
     * @param int $commentWeight
     */
    public function setCommentWeight($commentWeight)
    {
        $this->commentWeight = (float) $commentWeight;
    }

    /**
     * @return int
     */
    public function getCommentWeight()
    {
        return $this->commentWeight;
    }
}