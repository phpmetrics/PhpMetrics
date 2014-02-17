<?php

/*
 * (c) Jean-François Lépine <https://twitter.com/Halleck45>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Hal\Command\Job;
use Hal\Coupling\Coupling;
use Hal\Coupling\FileCoupling;
use Hal\File\Finder;
use Hal\OOP\Extractor\ClassMap;
use Hal\OOP\Extractor\Extractor;
use Hal\OOP\Extractor\Result;
use Hal\Result\ResultCollection;
use Symfony\Component\Console\Helper\ProgressHelper;
use Symfony\Component\Console\Output\OutputInterface;


/**
 * Starts analyze
 *
 * @author Jean-François Lépine <https://twitter.com/Halleck45>
 */
class DoAnalyze implements JobInterface
{

    /**
     * Path to analyze
     *
     * @var string
     */
    private $path;

    /**
     * Output
     *
     * @var \Symfony\Component\Console\Output\OutputInterface
     */
    private $output;

    /**
     * Finder
     *
     * @var Finder
     */
    private $finder;

    /**
     * do OOP analyze ?
     *
     * @var bool
     */
    private $withOOP;

    /**
     * Constructor
     *
     * @param OutputInterface $output
     * @param Finder $finder
     * @param string $path
     * @param bool $withOOP
     */
    function __construct(OutputInterface $output, Finder $finder, $path, $withOOP)
    {
        $this->output = $output;
        $this->finder = $finder;
        $this->path = $path;
        $this->withOOP = $withOOP;
    }

    /**
     * @inheritdoc
     */
    public function execute(ResultCollection $collection) {

        $files = $this->finder->find($this->path);

        if(0 == sizeof($files, COUNT_NORMAL)) {
            throw new \LogicException('No file found');
        }

        $progress = new ProgressHelper();
        $progress->start($this->output, sizeof($files, COUNT_NORMAL));

        // class map
        $classMap = new ClassMap();

        foreach($files as $filename) {

            $progress->advance();

            // HALSTEAD
            $halstead = new \Hal\Halstead\Halstead(new \Hal\Token\TokenType());
            $rHalstead = $halstead->calculate($filename);

            // LOC
            $loc = new \Hal\Loc\Loc();
            $rLoc = $loc->calculate($filename);

            // Maintenability Index
            $maintenability = new \Hal\MaintenabilityIndex\MaintenabilityIndex;
            $rMaintenability = $maintenability->calculate($rHalstead, $rLoc);


            // formats
            $resultSet = new \Hal\Result\ResultSet($filename);
            $resultSet
                ->setLoc($rLoc)
                ->setHalstead($rHalstead)
                ->setMaintenabilityIndex($rMaintenability);

            if($this->withOOP) {
                // OOP
                $extractor = new Extractor();
                $rOOP = $extractor->extract($filename);
                $classMap->push($filename, $rOOP);
                $resultSet->setOOP($rOOP);
            }


            $collection->push($resultSet);
        }
        $progress->clear();
        $progress->finish();

        if($this->withOOP) {
            // COUPLING (should be done after parsing files)
            $this->output->writeln('Analyzing coupling. This will take few minutes...');

            $coupling = new Coupling();
            $couplingMap = $coupling->calculate($classMap);

            // link between coupling and files
            $fileCoupling = new FileCoupling($classMap, $couplingMap);
            foreach($files as $filename) {
                $rCoupling = $fileCoupling->calculate($filename);
                $collection->get($filename)->setCoupling($rCoupling);
            }
        }
    }

}
