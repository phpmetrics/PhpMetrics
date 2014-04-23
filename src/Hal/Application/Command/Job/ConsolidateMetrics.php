<?php

/*
 * (c) Jean-François Lépine <https://twitter.com/Halleck45>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Hal\Application\Command\Job;
use Hal\Component\Aggregator\Aggregator;
use Hal\Component\Bounds\DirectoryAgregator;
use Hal\Component\Result\ResultCollection;
use Symfony\Component\Console\Output\OutputInterface;


/**
 * Consolidate metrics
 *
 * @author Jean-François Lépine <https://twitter.com/Halleck45>
 */
class ConsolidateMetrics implements JobInterface
{
    /**
     * Output
     *
     * @var \Symfony\Component\Console\Output\OutputInterface
     */
    private $output;

    /**
     * Aggregator of results
     *
     * @var Aggregator
     */
    private $aggregator;

    /**
     * Constructor
     *
     * @param OutputInterface $output
     * @param Aggregator $aggregator
     */
    public function __construct(OutputInterface $output, Aggregator $aggregator)
    {
        $this->output = $output;
        $this->aggregator= $aggregator;
    }

    /**
     * @inheritdoc
     */
    public function execute(ResultCollection $collection, ResultCollection $consolidated) {

        $queue = new Queue();
        $queue
            ->push(new Analyze\Aggregated\Aggregates($this->aggregator))
            ->push(new Analyze\Aggregated\DoAnalyze($this->aggregator))
        ;
        $queue->execute($collection, $consolidated);
    }

}
