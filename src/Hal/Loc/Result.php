<?php

/*
 * (c) Jean-François Lépine <https://twitter.com/Halleck45>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Hal\Loc;
use Hal\Result\ExportableInterface;

/**
 * Representation of LOC
 *
 * @author Jean-François Lépine <https://twitter.com/Halleck45>
 */
class Result implements ExportableInterface {

    /**
     * Lines of code
     *
     * @var integer
     */
    private $loc;

    /**
     * Lines of comments
     *
     * @var integer
     */
    private $commentLoc;

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
     * @inheritdoc
     */
    public function asArray() {
        return array (
            'cyclomaticComplexity' => $this->getComplexityCyclomatic()
            ,'loc' => $this->getLoc()
            ,'logicalLoc' => $this->getLogicalLoc()
        );
    }

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

    /**
     * @param int $commentLoc
     */
    public function setCommentLoc($commentLoc)
    {
        $this->commentLoc = $commentLoc;
        return $this;
    }

    /**
     * @return int
     */
    public function getCommentLoc()
    {
        return $this->commentLoc;
    }
}