<?php

/*
 * (c) Jean-François Lépine <https://twitter.com/Halleck45>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Hal\Command\Job;
use Hal\File\Finder;
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
     * Constructor
     *
     * @param OutputInterface $output
     * @param Finder $finder
     * @param Path $path
     */
    function __construct(OutputInterface $output, Finder $finder, $path)
    {
        $this->output = $output;
        $this->finder = $finder;
        $this->path = $path;
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

        foreach($files as $filename) {

            $progress->advance();

            // calculates
            $halstead = new \Hal\Halstead\Halstead(new \Hal\Token\TokenType());
            $rHalstead = $halstead->calculate($filename);

            $loc = new \Hal\Loc\Loc();
            $rLoc = $loc->calculate($filename);

            $maintenability = new \Hal\MaintenabilityIndex\MaintenabilityIndex;
            $rMaintenability = $maintenability->calculate($rHalstead, $rLoc);

            // formats
            $resultSet = new \Hal\Result\ResultSet(basename($this->path) . str_replace($this->path, '', $filename));
            $resultSet
                ->setLoc($rLoc)
                ->setHalstead($rHalstead)
                ->setMaintenabilityIndex($rMaintenability);

            $collection->push($resultSet);
        }

        $progress->clear();
        $progress->finish();
    }

}
