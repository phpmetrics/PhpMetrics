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
use Hal\Component\File\Finder;
use Hal\Component\File\SyntaxChecker;
use Hal\Metrics\Complexity\Text\Halstead\Halstead;
use Hal\Metrics\Complexity\Text\Length\Loc;
use Hal\Metrics\Design\Component\MaintenabilityIndex\MaintenabilityIndex;
use Hal\Metrics\Complexity\Component\McCabe\McCabe;
use Hal\Component\OOP\Extractor\ClassMap;
use Hal\Component\OOP\Extractor\Extractor;
use Hal\Component\OOP\Extractor\Result;
use Hal\Component\Result\ResultCollection;
use Hal\Component\Token\Tokenizer;
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
