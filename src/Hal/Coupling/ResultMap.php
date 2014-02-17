<?php

/*
 * (c) Jean-François Lépine <https://twitter.com/Halleck45>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Hal\Coupling;


/**
 * coupling map
 *
 * @author Jean-François Lépine <https://twitter.com/Halleck45>
 */
class ResultMap {

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
     * @return Result
     */
    public function get($class) {
        return isset($this->map[$class]) ? $this->map[$class] : null;
    }
}