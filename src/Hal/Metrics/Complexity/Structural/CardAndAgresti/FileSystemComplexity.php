<?php

/*
 * (c) Jean-François Lépine <https://twitter.com/Halleck45>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Hal\Metrics\Complexity\Structural\CardAndAgresti;
use Hal\Component\OOP\Extractor\ClassMap;


/**
 * Estimates System complexity for the given file
 *
 * @author Jean-François Lépine <https://twitter.com/Halleck45>
 */
class FileSystemComplexity {


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
     * Calculates system complexity
     *
     * @param string $filename
     * @return Result
     */
    public function calculate($filename)
    {
        $rOOP = $this->classMap->getClassesIn($filename);
        $result = new Result($filename);

        $len = 0;
        $sy = $dc = $sc = array();
        $calculator = new SystemComplexity();
        foreach($rOOP->getClasses() as $class) {
            $r = $calculator->calculate($class);

            $len += sizeof($class->getMethods(), COUNT_NORMAL);
            $sy[] = $r->getTotalSystemComplexity();
            $dc[] = $r->getTotalDataComplexity();
            $sc[] = $r->getTotalStructuralComplexity();
        }

        if($len > 0 &&sizeof($dc, COUNT_NORMAL) > 0) {
            $result
                ->setRelativeStructuralComplexity(round(array_sum($sc) / $len, 2))
                ->setRelativeDataComplexity(round(array_sum($dc) / $len, 2))
                ->setRelativeSystemComplexity(round(array_sum($sy) / $len, 2))
                ->setTotalStructuralComplexity(array_sum($sc))
                ->setTotalDataComplexity(array_sum($dc))
                ->setTotalSystemComplexity(array_sum($dc) + array_sum($sc))
            ;
        }
        return $result;
    }
};