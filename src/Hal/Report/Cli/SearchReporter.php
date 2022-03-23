<?php

namespace Hal\Report\Cli;

use Hal\Application\Config\Config;
use Hal\Component\Output\Output;
use Hal\Metric\ClassMetric;
use Hal\Metric\InterfaceMetric;
use Hal\Metric\Metrics;
use Hal\Metric\SearchMetric;
use Hal\Search\PatternSearcher;
use Hal\Search\Searches;

class SearchReporter
{
    /**
     * @var Config
     */
    private $config;

    /**
     * @var Output
     */
    private $output;

    /**
     * @param Config $config
     * @param Output $output
     */
    public function __construct(Config $config, Output $output)
    {
        $this->config = $config;
        $this->output = $output;
    }

    /**
     * @param Metrics $metrics
     */
    public function generate(Metrics $metrics)
    {
        /** @var SearchMetric $searches */
        $searches = $metrics->get('searches');
        if (empty($searches)) {
            return;
        }

        foreach ($searches->all() as $name => $search) {

            if (!is_array($search)) {
                continue;
            }

            $this->displayCliReport($name, $search);
        }
    }

    private function displayCliReport($searchName, array $foundSearch)
    {
        $title = sprintf(
            '<info>Found %d occurrences for search "%s"</info>',
            sizeof($foundSearch),
            $searchName
        );

        $config = $this->config->get('searches')->get($searchName)->getConfig();
        if(!empty($foundSearch) && !empty($config->failIfFound) && true === $config->failIfFound) {
            $title = sprintf(
                '<error>[ERR] Found %d occurrences for search "%s"</error>',
                sizeof($foundSearch),
                $searchName
            );
        }

        $sampleToDisplay = 5;
        $this->output->writeln($title);

        $parts = array_slice($foundSearch, 0, $sampleToDisplay);
        foreach ($parts as $part) {
            $this->output->writeln(sprintf('- %s', $part->getName()));
        }

        if (sizeof($foundSearch) > $sampleToDisplay) {
            $this->output->writeln(sprintf('... and %d more', sizeof($foundSearch) - $sampleToDisplay));
        }

        $this->output->writeln(PHP_EOL);
    }
}
