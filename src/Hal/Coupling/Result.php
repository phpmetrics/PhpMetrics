<?php

/*
 * (c) Jean-François Lépine <https://twitter.com/Halleck45>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Hal\Coupling;


/**
 * Represents coupling (for class)
 *
 * @author Jean-François Lépine <https://twitter.com/Halleck45>
 */
class Result {

    /**
     * @var array
     */
    private $map = array();

    /**
     * @param array $map
     */
    public function __construct(array $map)
    {
        $this->map = $map;
    }

    /**
     * @param $class
     * @return null
     */
    public function getAfferentCoupling($class) {
        return isset($this->map[$class]) ? $this->map[$class]->ca : null;
    }

    /**
     * @param $class
     * @return null
     */
    public function getEfferentCoupling($class) {
        return isset($this->map[$class]) ? $this->map[$class]->ce : null;
    }

    /**
     * @param $class
     * @return null
     */
    public function getInstability($class) {
        return isset($this->map[$class]) ? $this->map[$class]->instability : null;
    }
}