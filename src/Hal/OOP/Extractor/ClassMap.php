<?php

/*
 * (c) Jean-François Lépine <https://twitter.com/Halleck45>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Hal\OOP\Extractor;



/**
 * ResultMap
 *
 * @author Jean-François Lépine <https://twitter.com/Halleck45>
 */
class ClassMap {

    /**
     * @var array
     */
    private $classes = array();

    /**
     * Current file
     *
     * @var string
     */
    private $filename;

    /**
     * Associates result and filename
     *
     * @param $filename
     * @param Result $result
     * @return $this
     */
    public function push($filename, Result $result) {
        $this->classes[$filename] = $result;;
        return $this;
    }

    /**
     * Get for filename
     *
     * @param $filename
     * @return array
     */
    public function getClassesIn($filename) {
        return isset($this->classes[$filename]) ? $this->classes[$filename] : array();
    }

    /**
     * All classes
     *
     * @return array
     */
    public function all() {
        return $this->classes;
    }
};