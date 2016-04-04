<?php

/*
 * (c) Jean-François Lépine <https://twitter.com/Halleck45>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Hal\Application\Command\Job;
use Hal\Application\Extension\ExtensionService;
use Hal\Application\Formater\Chart;
use Hal\Application\Formater\Details;
use Hal\Application\Formater\Summary;
use Hal\Application\Formater\Violations;
use Hal\Application\Rule\Validator;
use Hal\Application\Score\Scoring;
use Hal\Component\Aggregator\DirectoryAggregatorFlat;
use Hal\Component\Bounds\BoundsInterface;
use Hal\Component\Config\ConfigurationInterface;
use Hal\Component\File\Finder;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Queue factory
 *
 * @author Jean-François Lépine <https://twitter.com/Halleck45>
 */
class QueueAnalyzeFactory
{

    /**
     * @var ConfigurationInterface
     */
    private $config;

    /**
     * @var OutputInterface
     */
    private $output;

    /**
     * @var InputInterface
     */
    private $input;

    /**
     * @var ExtensionService
     */
    private $extensionsService;

    /**
     * Constructor
     *
     * @param InputInterface $input
     * @param OutputInterface $output
     * @param ConfigurationInterface $config
     * @param ExtensionService $extensionService
     */
    public function __construct(InputInterface $input, OutputInterface $output, ConfigurationInterface $config, ExtensionService $extensionService)
    {
        $this->config = $config;
        $this->input = $input;
        $this->output = $output;
        $this->extensionsService = $extensionService;
    }

    /**
     * Factory queue
     *
     * @param Finder $finder
     * @param BoundsInterface $bounds
     * @return Queue
     */
    public function factory(Finder $finder, BoundsInterface $bounds) {
        $rules = $this->config->getRuleSet();
        $validator = new Validator($rules);

        // jobs queue planning
        $queue = new Queue;
        $queue
            ->push(new DoAnalyze($this->output, $finder, $this->config->getPath()->getBasePath(), !$this->input->getOption('without-oop'), $this->config->getIgnoreErrors()))
            ->push(new SearchBounds($this->output, $bounds))
            ->push(new DoAggregatedAnalyze($this->output, new DirectoryAggregatorFlat($this->input->getOption('level'))))
            ->push(new CalculateScore(new Scoring($bounds)))
        ;

        return $queue;
    }

}
