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
 * Starts analyze of one file
 *
 * @author Jean-François Lépine <https://twitter.com/Halleck45>
 */
class FileAnalyzer
{

    /**
     * Output
     *
     * @var \Symfony\Component\Console\Output\OutputInterface
     */
    private $output;

    /**
     * do OOP analyze ?
     *
     * @var bool
     */
    private $withOOP;

    /**
     * @var Halstead
     */
    private $halstead;

    /**
     * @var MaintenabilityIndex
     */
    private $maintenabilityIndex;

    /**
     * @var Loc
     */
    private $loc;

    /**
     * @var McCabe
     */
    private $mcCabe;

    /**
     * @var Extractor
     */
    private $extractor;

    /**
     * @var \Hal\OOP\Extractor\ClassMap
     */
    private $classMap;

    /**
     * Constructor
     *
     * @param OutputInterface $output
     * @param $withOOP
     * @param Extractor $extractor
     * @param Halstead $halstead
     * @param Loc $loc
     * @param MaintenabilityIndex $maintenabilityIndex
     * @param McCabe $mcCabe
     * @param ClassMap $classMap
     */
    public function __construct(
        OutputInterface $output
        , $withOOP
        , Extractor $extractor
        , Halstead $halstead
        , Loc $loc
        , MaintenabilityIndex $maintenabilityIndex
        , McCabe $mcCabe
        , ClassMap $classMap
    )
    {
        $this->extractor = $extractor;
        $this->halstead = $halstead;
        $this->loc = $loc;
        $this->maintenabilityIndex = $maintenabilityIndex;
        $this->mcCabe = $mcCabe;
        $this->output = $output;
        $this->withOOP = $withOOP;
        $this->classMap = $classMap;
    }


    /**
     * Run analyze
     * @return \Hal\Result\ResultSet
     */
    public function execute($filename) {

        $rHalstead = $this->halstead->calculate($filename);
        $rLoc = $this->loc->calculate($filename);
        $rMcCabe = $this->mcCabe->calculate($filename);
        $rMaintenability = $this->maintenabilityIndex->calculate($rHalstead, $rLoc, $rMcCabe);

        $resultSet = new \Hal\Result\ResultSet($filename);
        $resultSet
            ->setLoc($rLoc)
            ->setMcCabe($rMcCabe)
            ->setHalstead($rHalstead)
            ->setMaintenabilityIndex($rMaintenability);

        if($this->withOOP) {
            $rOOP = $this->extractor->extract($filename);
            $this->classMap->push($filename, $rOOP);
            $resultSet->setOOP($rOOP);
        }

        return $resultSet;
    }

}
