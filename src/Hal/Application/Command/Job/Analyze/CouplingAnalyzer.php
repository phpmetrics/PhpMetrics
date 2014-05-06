<?php

/*
 * (c) Jean-François Lépine <https://twitter.com/Halleck45>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Hal\Application\Command\Job\Analyze;
use Hal\Metrics\Complexity\Structural\HenryAndKafura\Coupling;
use Hal\Metrics\Complexity\Structural\HenryAndKafura\FileCoupling;
use Hal\Component\OOP\Extractor\ClassMap;
use Hal\Component\Result\ResultCollection;


/**
 * Starts analyze of coupling
 *
 * @author Jean-François Lépine <https://twitter.com/Halleck45>
 */
class CouplingAnalyzer
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
        $coupling = new Coupling();
        $couplingMap = $coupling->calculate($this->classMap);

        // link between coupling and files
        $fileCoupling = new FileCoupling($this->classMap, $couplingMap);
        foreach($files as $filename) {
            $rCoupling = $fileCoupling->calculate($filename);
            $this->collection->get($filename)->setCoupling($rCoupling);
        }
    }

}
