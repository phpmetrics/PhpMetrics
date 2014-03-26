<?php

/*
 * (c) Jean-François Lépine <https://twitter.com/Halleck45>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Hal\Command\Job\Analyze;
use Hal\Coupling\Coupling;
use Hal\Coupling\FileCoupling;
use Hal\File\Finder;
use Hal\File\SyntaxChecker;
use Hal\Halstead\Halstead;
use Hal\Loc\Loc;
use Hal\MaintenabilityIndex\MaintenabilityIndex;
use Hal\McCabe\McCabe;
use Hal\OOP\Extractor\ClassMap;
use Hal\OOP\Extractor\Extractor;
use Hal\OOP\Extractor\Result;
use Hal\Result\ResultCollection;
use Hal\Token\Tokenizer;
use Symfony\Component\Console\Helper\ProgressHelper;
use Symfony\Component\Console\Output\OutputInterface;


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
