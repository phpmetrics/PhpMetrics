<?php

/*
 * (c) Jean-François Lépine <https://twitter.com/Halleck45>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Hal\Coupling;
use Hal\OOP\Extractor\ClassMap;
use Hal\OOP\Extractor\Result as PooResult;
use Hal\Token\Token;


/**
 * Estimates coupling (based on work of Henry And Kafura)
 *
 * @author Jean-François Lépine <https://twitter.com/Halleck45>
 */
class Coupling {

    /**
     * Calculates coupling
     *
     * @param \Hal\OOP\Extractor\Result $result
     * @return ResultMap
     */
    public function calculate(ClassMap $result)
    {
        $map = $this->extractCoupling($result);

        // instability
        foreach($map as &$class) {
            if($class->getAfferentCoupling() + $class->getEfferentCoupling() > 0) {
                $class->setInstability($class->getEfferentCoupling() / ($class->getAfferentCoupling() + $class->getEfferentCoupling()));
            }
        }

        return new ResultMap($map);
    }


    /**
     * Extracts afferent and efferent coupling
     *
     * @param PooResult $result
     * @return array
     */
    private function extractCoupling(ClassMap $result) {
        $results = $result->all();

        $classes = array();
        foreach($results as $result) {
            $classes = array_merge($classes, $result->getClasses());
        }

        $map = array();
        foreach($classes as $class) {

            if(!isset($map[$class->getName()])) {
                $map[$class->getName()] = new Result($class->getName());
            }

            $dependencies = $class->getDependencies();
            $map[$class->getName()]->setEfferentCoupling(sizeof($dependencies, COUNT_NORMAL));

            foreach($dependencies as $dependency) {

                if(!isset($map[$dependency])) {
                    $map[$dependency] = new Result($dependency);
                }
                $map[$dependency]->setAfferentCoupling($map[$dependency]->getAfferentCoupling() + 1);
            }
        }
        return $map;
    }
};