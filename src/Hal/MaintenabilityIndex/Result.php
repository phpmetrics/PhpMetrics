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
     * Maintenability index without comment
     * Designed in 1991 by Paul Oman and Jack Hagemeister at the University of Idaho
     *
     * @var float
     */
    private $maintenabilityIndexWithoutComment;


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
            'maintenabilityIndexWithoutComment' => (string) $this->getMaintenabilityIndexWithoutComment()
            , 'maintenabilityIndex' => (string) $this->getMaintenabilityIndex()
            , 'commentWeight' => (float) $this->getCommentWeight()
        );
    }

    /**
     * @return float
     */
    public function getMaintenabilityIndex()
    {
        return $this->maintenabilityIndexWithoutComment + $this->commentWeight;
    }

    /**
     * @param float $maintenabilityIndexWithoutComment
     */
    public function setMaintenabilityIndexWithoutComment($maintenabilityIndexWithoutComment)
    {
        if(is_infinite($maintenabilityIndexWithoutComment)) {
            $maintenabilityIndexWithoutComment = 171;
        }
        $this->maintenabilityIndexWithoutComment = round($maintenabilityIndexWithoutComment,2);

    }
    /**
     * @return float
     */
    public function getMaintenabilityIndexWithoutComment()
    {
        return $this->maintenabilityIndexWithoutComment;
    }

    /**
     * @param int $commentWeight
     */
    public function setCommentWeight($commentWeight)
    {
        $this->commentWeight = (float) round($commentWeight, 2);
    }

    /**
     * @return int
     */
    public function getCommentWeight()
    {
        return $this->commentWeight;
    }
}