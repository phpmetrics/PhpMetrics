<?php

/*
 * (c) Jean-François Lépine <https://twitter.com/Halleck45>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Hal\Application\Command\Job\Analyze;

use Hal\Component\OOP\Extractor\ClassMap;
use Hal\Component\Result\ResultCollection;
use Hal\Metrics\Complexity\Structural\LCOM\FileLackOfCohesionOfMethods;


/**
 * Starts analyze of Lack of cohesion of methods
 *
 * @author Jean-François Lépine <https://twitter.com/Halleck45>
 */
class LcomAnalyzer
{

    /**
     * @var ClassMap
     */
    private $classMap;

    /**
     * @var ResultCollection
     */
    private $collection;

    /**
     * Constructor
     *
     * @param ClassMap $classMap
     * @param ResultCollection $collection
     */
    public function __construct(ClassMap $classMap, ResultCollection $collection)
    {
        $this->classMap = $classMap;
        $this->collection = $collection;
    }


    public function execute(array $files) {
        $fileCoupling = new FileLackOfCohesionOfMethods($this->classMap);
        foreach($files as $filename) {
            $result = $fileCoupling->calculate($filename);
            $this->collection->get($filename)->setLcom($result);
        }
    }

}
