<?php

/*
 * (c) Jean-François Lépine <https://twitter.com/Halleck45>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Hal\Metrics\Design\Component\MaintainabilityIndex;
use Hal\Component\Result\ExportableInterface;


/**
 * Representation of Maintainability Index
 *
 * @author Jean-François Lépine <https://twitter.com/Halleck45>
 */
class Result implements ExportableInterface {

    /**
     * Maintainability index without comment
     * Designed in 1991 by Paul Oman and Jack Hagemeister at the University of Idaho
     *
     * @var float
     */
    private $maintainabilityIndexWithoutComment;


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
            'maintainabilityIndexWithoutComment' => (string) $this->getMaintainabilityIndexWithoutComment()
            , 'maintainabilityIndex' => (string) $this->getMaintainabilityIndex()
            , 'commentWeight' => (float) $this->getCommentWeight()
        );
    }

    /**
     * @return float
     */
    public function getMaintainabilityIndex()
    {
        return $this->maintainabilityIndexWithoutComment + $this->commentWeight;
    }

    /**
     * @param float $maintainabilityIndexWithoutComment
     */
    public function setMaintainabilityIndexWithoutComment($maintainabilityIndexWithoutComment)
    {
        if(is_infinite($maintainabilityIndexWithoutComment)) {
            $maintainabilityIndexWithoutComment = 171;
        }
        $this->maintainabilityIndexWithoutComment = round($maintainabilityIndexWithoutComment,2);

    }
    /**
     * @return float
     */
    public function getMaintainabilityIndexWithoutComment()
    {
        return $this->maintainabilityIndexWithoutComment;
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