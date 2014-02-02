<?php

/*
 * (c) Jean-François Lépine <https://twitter.com/Halleck45>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Hal\Bounds\Result;
use Hal\Result\ResultBoundary;

/**
 * Boundary for directory (proxy of boundary)
 *
 * @author Jean-François Lépine <https://twitter.com/Halleck45>
 */
class DirectoryResult {

    /**
     * @var BoundsResult
     */
    private $bounds;

    /**
     * @var string
     */
    private $directory;


    /**
     * @param $directory
     * @param BoundsResult $bounds
     */
    public function __construct($directory, BoundsResult $bounds) {
        $this->bounds = $bounds;
        $this->directory = $directory;
    }

    /**
     * @return string
     */
    public function getDirectory() {
        return $this->directory;
    }

    /**
     * @return string
     */
    public function getDepth() {
        return substr_count($this->directory, DIRECTORY_SEPARATOR) - 1;
    }

    /**
     * @inheritdoc
     */
    public function getSum($key) {
        return $this->bounds->getSum($key);
    }

    /**
     * @inheritdoc
     */
    public function getMin($key) {
        return $this->bounds->getMin($key);
    }

    /**
     * @inheritdoc
     */
    public function getMax($key) {
        return $this->bounds->getMax($key);
    }

    /**
     * @inheritdoc
     */
    public function getAverage($key) {
        return $this->bounds->getAverage($key);
    }

}