<?php

/*
 * (c) Jean-François Lépine <https://twitter.com/Halleck45>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Hal\Application\Command\Job;
use Hal\Application\Formater\Chart;
use Hal\Application\Formater\Details;
use Hal\Application\Formater\Summary;
use Hal\Application\Formater\Violations;
use Hal\Application\Score\ScoreInterface;
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
class QueueFactory
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
     * Constructor
     *
     * @param InputInterface $input
     * @param OutputInterface $output
     * @param ConfigurationInterface $config
     */
    public function __construct(InputInterface $input, OutputInterface $output, ConfigurationInterface $config)
    {
        $this->config = $config;
        $this->input = $input;
        $this->output = $output;
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
        $validator = new \Hal\Application\Rule\Validator($rules);

        // jobs queue planning
        $queue = new Queue;
        $queue
            ->push(new DoAnalyze($this->output, $finder, $this->config->getPath()->getBasePath(), !$this->input->getOption('without-oop')))
            ->push(new SearchBounds($this->output, $bounds))
            ->push(new DoAggregatedAnalyze($this->output, new DirectoryAggregatorFlat($this->input->getOption('level'))))
            ->push(new CalculateScore(new Scoring($bounds)))
            ->push(new ReportRenderer(true, $this->output, new Summary\Cli($validator, $bounds)))
            ->push(new ReportRenderer($this->config->getLogging()->getReport('cli'), $this->output, new Details\Cli($validator, $bounds)))
            ->push(new ReportWriter($this->config->getLogging()->getReport('html'), $this->output, new Summary\Html($validator, $bounds)))
            ->push(new ReportWriter($this->config->getLogging()->getReport('json'), $this->output, new Details\Json($validator, $bounds)))
            ->push(new ReportWriter($this->config->getLogging()->getReport('xml'), $this->output, new Summary\Xml($validator, $bounds)))
            ->push(new ReportWriter($this->config->getLogging()->getReport('csv'), $this->output, new Details\Csv($validator, $bounds)))
            ->push(new ReportWriter($this->config->getLogging()->getViolation('xml'), $this->output, new Violations\Xml($validator, $bounds)))
            ->push(new ReportWriter($this->config->getLogging()->getChart('bubbles'), $this->output, new Chart\Bubbles($validator, $bounds)));

        return $queue;
    }

}
