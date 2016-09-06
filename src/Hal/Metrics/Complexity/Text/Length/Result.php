<?php

/*
 * (c) Jean-François Lépine <https://twitter.com/Halleck45>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Hal\Metrics\Complexity\Text\Length;
use Hal\Component\Result\ExportableInterface;

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
     * Size of the file
     *
     * @var integer
     */
    private $filesize;

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
            'loc' => $this->getLoc()
            ,'logicalLoc' => $this->getLogicalLoc()
            ,'filesize' => $this->getFilesize()
        );
    }

    /**
     * @param int $complexityCyclomatic
     * @return $this
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
     * @return $this
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
     * @param int $filesize
     * @return $this
     */
    public function setFilesize($filesize)
    {
        $this->filesize = $filesize;
        return $this;
    }

    /**
     * @return int
     */
    public function getfilesize()
    {
        return $this->filesize;
    }

    /**
     * @param int $logicalLoc
     * @return $this
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
     * @return $this
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