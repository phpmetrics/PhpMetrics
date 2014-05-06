<?php

/*
 * (c) Jean-François Lépine <https://twitter.com/Halleck45>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Hal\Application\Command\Job\Analyze;
use Hal\Metrics\Complexity\Component\Myer\Myer;
use Hal\Metrics\Complexity\Text\Halstead\Halstead;
use Hal\Metrics\Complexity\Text\Length\Loc;
use Hal\Metrics\Design\Component\MaintenabilityIndex\MaintenabilityIndex;
use Hal\Metrics\Complexity\Component\McCabe\McCabe;
use Hal\Component\OOP\Extractor\ClassMap;
use Hal\Component\OOP\Extractor\Extractor;
use Hal\Component\OOP\Extractor\Result;
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
     * @var Myer
     */
    private $myer;

    /**
     * @var Extractor
     */
    private $extractor;

    /**
     * @var \Hal\Component\OOP\Extractor\ClassMap
     */
    private $classMap;

    /**
     * Constructor
     *
     * @param OutputInterface $output
     * @param boolean $withOOP
     * @param Extractor $extractor
     * @param Halstead $halstead
     * @param Loc $loc
     * @param MaintenabilityIndex $maintenabilityIndex
     * @param McCabe $mcCabe
     * @param Myer $myer
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
        , Myer $myer
        , ClassMap $classMap
    )
    {
        $this->extractor = $extractor;
        $this->halstead = $halstead;
        $this->loc = $loc;
        $this->maintenabilityIndex = $maintenabilityIndex;
        $this->mcCabe = $mcCabe;
        $this->myer = $myer;
        $this->output = $output;
        $this->withOOP = $withOOP;
        $this->classMap = $classMap;
    }


    /**
     * Run analyze
     * @return \Hal\Component\Result\ResultSet
     */
    public function execute($filename) {

        $rHalstead = $this->halstead->calculate($filename);
        $rLoc = $this->loc->calculate($filename);
        $rMcCabe = $this->mcCabe->calculate($filename);
        $rMyer = $this->myer->calculate($filename);
        $rMaintenability = $this->maintenabilityIndex->calculate($rHalstead, $rLoc, $rMcCabe);

        $resultSet = new \Hal\Component\Result\ResultSet($filename);
        $resultSet
            ->setLoc($rLoc)
            ->setMcCabe($rMcCabe)
            ->setMyer($rMyer)
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
