<?php

/*
 * (c) Jean-François Lépine <https://twitter.com/Halleck45>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Hal\Coupling;
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
     * @return Result
     */
    public function calculate(PooResult $result)
    {
        $map = $this->extractCoupling($result);

        // instability
        foreach($map as &$class) {
            $class->instability = $class->ce / ($class->ca + $class->ce);
        }

        return new Result($map);
    }


    /**
     * Extracts afferent and efferent coupling
     *
     * @param Result $result
     * @return array
     */
    private function extractCoupling(PooResult $result) {
        $classes = $result->getClasses();
        $map = array();
        foreach($classes as $class) {

            if(!isset($map[$class->getName()])) {
                $map[$class->getName()] = (object) array('ce' => 0, 'ca' => 0);
            }

            $dependencies = $class->getDependencies();
            $map[$class->getName()]->ce = sizeof($dependencies, COUNT_NORMAL);

            foreach($dependencies as $dependency) {

                if(!isset($map[$dependency])) {
                    $map[$dependency] = (object) array('ce' => 0, 'ca' => 0);
                }
                $map[$dependency]->ca++;
            }
        }
        return $map;
    }
};