<?php

/*
 * (c) Jean-François Lépine <https://twitter.com/Halleck45>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Hal\Metrics\Complexity\Structural\HenryAndKafura;
use Hal\Component\OOP\Extractor\ClassMap;


/**
 * Estimates coupling by file: average for all declared classes in this file
 *
 * @author Jean-François Lépine <https://twitter.com/Halleck45>
 */
class FileCoupling {


    /**
     * @var \Hal\Component\OOP\Extractor\ClassMap
     */
    private $classMap;

    /**
     * @var CouplingMap
     */
    private $couplingMap;

    /**
     * Constructor
     *
     * @param ClassMap $classMap
     * @param ResultMap $couplingMap
     */
    public function __construct(ClassMap $classMap, ResultMap $couplingMap)
    {
        $this->classMap = $classMap;
        $this->couplingMap = $couplingMap;
    }


    /**
     * Calculates coupling for file (and not for class)
     *
     * @param string $filename
     * @return Result
     */
    public function calculate($filename)
    {
        $rOOP = $this->classMap->getClassesIn($filename);
        $result = new Result($filename);

        $instability = $ce = $ca = 0;

        $classes = $rOOP->getClasses();
        foreach($classes as $declaredClass) {
            $declaredClassCoupling = $this->couplingMap->get($declaredClass->getName());

            $ce += $declaredClassCoupling->getEfferentCoupling();
            $ca += $declaredClassCoupling->getAfferentCoupling();
        }

        if($ca + $ce > 0) {
            $instability = $ce / ($ca + $ce);
        }

        $result
            ->setAfferentCoupling($ca)
            ->setEfferentCoupling($ce)
            ->setInstability($instability);

        return $result;
    }
};