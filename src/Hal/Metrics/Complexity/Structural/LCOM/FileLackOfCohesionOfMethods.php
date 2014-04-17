<?php

/*
 * (c) Jean-François Lépine <https://twitter.com/Halleck45>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Hal\Metrics\Complexity\Structural\LCOM;
use Hal\Component\OOP\Extractor\ClassMap;
use Hal\Component\Token\Tokenizer;


/**
 * Estimates LCOM by file: average for all declared classes in this file
 *
 * @author Jean-François Lépine <https://twitter.com/Halleck45>
 */
class FileLackOfCohesionOfMethods {


    /**
     * @var \Hal\Component\OOP\Extractor\ClassMap
     */
    private $classMap;

    /**
     * Constructor
     *
     * @param ClassMap $classMap
     */
    public function __construct(ClassMap $classMap)
    {
        $this->classMap = $classMap;
    }


    /**
     * Calculates lcom for file (and not for class)
     *
     * @param string $filename
     * @return Result
     */
    public function calculate($filename)
    {
        $rOOP = $this->classMap->getClassesIn($filename);
        $result = new Result($filename);

        $n = 0;
        $lcom = new LackOfCohesionOfMethods();

        foreach($rOOP->getClasses() as $class) {
            $r = $lcom->calculate($class);
            $n += $r->getLcom();
        }

        $result->setLcom($n);

        return $result;
    }
};