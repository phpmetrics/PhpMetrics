<?php

/*
 * (c) Jean-François Lépine <https://twitter.com/Halleck45>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Hal\Application\Command\Job;

use Hal\Application\Command\Job\Analyze\CouplingAnalyzer;
use Hal\Application\Command\Job\JobInterface;
use Hal\Component\Aggregator\Aggregator;
use Hal\Component\Bounds\Bounds;
use Hal\Component\OOP\Extractor\ClassMap;
use Hal\Component\OOP\Extractor\Extractor;
use Hal\Component\Result\AggregateResultSet;
use Hal\Component\Result\ResultAggregate;
use Hal\Component\Result\ResultCollection;
use Hal\Component\Token\Tokenizer;
use Hal\Metrics\Complexity\Structural\HenryAndKafura\Coupling;
use Hal\Metrics\Mood\Abstractness\Abstractness;
use Hal\Metrics\Mood\Instability\Instability;
use Symfony\Component\Console\Output\OutputInterface;


/**
 * @author Jean-François Lépine <https://twitter.com/Halleck45>
 */
class DoAggregatedAnalyze implements JobInterface
{

    /**
     * Aggregator of results
     *
     * @var Aggregator
     */
    private $aggregator;

    /**
     * Output
     *
     * @var OutputInterface
     *
     */
    private $output;
    /**
     * Constructor
     *
     * @param OutputInterface $output
     * @param Aggregator $aggregator
     */
    public function __construct(OutputInterface $output, Aggregator $aggregator)
    {
        $this->aggregator= $aggregator;
        $this->output = $output;
    }


    /**
    * @inheritdoc
    */
    public function execute(ResultCollection $collection, ResultCollection $aggregatedResults) {

        $this->output->write(str_pad("\x0DGrouping results by package/directory. This will take few minutes...", 80, "\x20"));

        // Aggregates results
        $groupedResults = $this->aggregator->aggregates($collection);

        // metrics tools
        $bounds = new Bounds();

        foreach($groupedResults as $namespace => $results) {
            $resultAggregate = new ResultAggregate($namespace);
            $resultAggregate
                ->setBounds($bounds->calculate($results))
            ;


            $aggregatedResults->push($resultAggregate);
        }
    }

}
