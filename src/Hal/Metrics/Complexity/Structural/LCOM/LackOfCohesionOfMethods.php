<?php

/*
 * (c) Jean-François Lépine <https://twitter.com/Halleck45>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Hal\Metrics\Complexity\Structural\LCOM;
use Hal\Component\Reflected\Klass;
use Hal\Component\Reflected\Method;
use Hal\Metrics\ClassMetric;

/**
 * Calculates lack of cohesion method
 *
 * @author Jean-François Lépine <https://twitter.com/Halleck45>
 */
class LackOfCohesionOfMethods implements ClassMetric {

    /**
     * Calculate Lack of cohesion of methods (LCOM)
     *
     *      We have choose the LCOM4 extension (Hitz and Montazeri version)
     *
     * @param Klass $class
     * @return Result
     */
    public function calculate(Klass $class)
    {

        $result = new Result;
        $lcom = 0;
        $methodsToInspect = $class->getMethods();

        $toSkip = array();
        foreach($methodsToInspect as $method) {

            if(in_array($method->getName(), $toSkip)) {
                continue;
            }

            if($method->isSetter() ||$method->isGetter()) {
                continue;
            }

            $linked = $this->getLinkedMethods($class, $method, $toSkip);
            $toSkip = array_merge($toSkip, $linked);
            $lcom++;

        }

        $result->setLcom($lcom);
        return $result;
    }

    /**
     * Search direct and indirect linked methods
     *
     * @param Klass $class
     * @param Method $method
     * @param array $toSkip (avoid infinite loops)
     * @return array
     */
    private function getLinkedMethods(Klass $class, Method $method, array &$toSkip = array()) {

        $linked = array_merge(
            // search directly called methods
            $this->searchDirectLinkedMethodsByCall($class, $method)
            // search directly linked by member method
            , $this->searchDirectLinkedMethodsByMember($class, $method)
        );

        // avoid infinite loop
        $linked = array_diff($linked, $toSkip);
        $toSkip = array_merge($toSkip, $linked, array($method->getName()));


        // foreach directly linked methods, recurs
        $methods = $class->getMethods();
        foreach($linked as $link) {
            if(!isset( $methods[$link])) {
                continue;
            }
            $linked = array_merge($linked, $this->getLinkedMethods($class, $methods[$link], $toSkip));
        }

        return $linked;
    }

    /**
     * Search methods whose are called or call this method
     *
     * @param Klass $class
     * @param Method $method
     * @return array
     */
    private function searchDirectLinkedMethodsByCall(Klass $class, Method $method) {
        $linked = array();

        // A calls B
        if(preg_match_all('!\\$this\\->(\w*?)\\(!im', $method->getContent(), $matches)) {
            list(, $linked) = $matches;
        }

        // B calls A
        foreach($class->getMethods() as $otherMethod) {
            $otherCalls = array();
            if(preg_match_all('!\\$this\\->(\w*?)\\(!im', $otherMethod->getContent(), $matches)) {
                list(, $otherCalls) = $matches;
            }

            if(in_array($method->getName(), $otherCalls)) {
                array_push($linked, $otherMethod->getName());
            }
        }
        return $linked;
    }

    /**
     * Search methods whose share attribute
     *
     * @param Klass $class
     * @param Method $method
     * @return array
     */
    private function searchDirectLinkedMethodsByMember(Klass $class, Method $method) {

        $linked = array();
        $members = array();
        if(preg_match_all('!\\$this\\->([\w\\(]+)!im', $method->getContent(), $matches)) {
            list(, $members) = $matches;
        }


        // search in other methods if they share attribute
        foreach($class->getMethods() as $otherMethod) {
            $otherMembers = array();
            if(preg_match_all('!\\$this\\->([\w\\(]+)!im', $otherMethod->getContent(), $matches)) {
                list(, $otherMembers) = $matches;
            }

            $intersect = array_intersect($members, $otherMembers);

            // remove calls (members and calls are mixed : regex is too complex to be read)
            foreach($intersect as $k => $name) {
                if(preg_match('!\($!', $name)) {
                    unset($intersect[$k]);
                }
            }


            if(sizeof($intersect, COUNT_NORMAL) > 0) {

                array_push($linked, $otherMethod->getName());
            }
        }
        return $linked;
    }
}





