<?php

/*
 * (c) Jean-François Lépine <https://twitter.com/Halleck45>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Hal\OOP\Extractor;
use Hal\OOP\Reflected\ReflectedClass;
use Hal\Result\ExportableInterface;


/**
 * Result
 *
 * @author Jean-François Lépine <https://twitter.com/Halleck45>
 */
class Result implements ExportableInterface {

    /**
     * @var array
     */
    private $classes = array();

    /**
     * @inheritdoc
     */
    public function asArray() {
        return array(
            'noc' => sizeof($this->classes, COUNT_NORMAL)
        );
    }

    /**
     * Push class
     *
     * @param ReflectedClass $class
     * @return $this
     */
    public function pushClass(ReflectedClass $class) {
        array_push($this->classes, $class);
        return $this;
    }

    /**
     * @return array
     */
    public function getClasses()
    {
        return $this->classes;
    }
};