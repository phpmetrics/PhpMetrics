<?php

/*
 * (c) Jean-François Lépine <https://twitter.com/Halleck45>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Hal\Bounds\Result;
use Hal\Result\ExportableInterface;

/**
 * Boundary for directory (proxy of boundary)
 *
 * @author Jean-François Lépine <https://twitter.com/Halleck45>
 */
class DirectoryResult implements ExportableInterface, ResultInterface {

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
     * @return integer
     */
    public function getDepth() {
        return max(0, substr_count($this->directory, DIRECTORY_SEPARATOR) - 1);
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

    /**
     * @inheritdoc
     */
    public function asArray() {
        return array_merge($this->bounds->asArray(), array(
            'directory' => $this->directory
            , 'depth' => $this->getDepth()
        ));
    }

    /**
     * @inheritdoc
     */
    public function has($key) {
        return $this->bounds->has($key);
    }

    /**
     * @inheritdoc
     */
    public function get($type, $key)
    {
        switch($type) {
            case 'max':
                return $this->getMax($key);
            case 'sum':
                return $this->getSum($key);
            case 'min':
                return $this->getMin($key);
            case 'average':
                return $this->getAverage($key);
        }
        return null;
    }
}