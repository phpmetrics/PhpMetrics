<?php

namespace Hal\Report\Json;

use Hal\Application\Config\Config;
use Hal\Component\Output\Output;
use Hal\Metric\Consolidated;
use Hal\Metric\Metrics;
use Hal\Report\SummaryProvider;

class SummaryReporter
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

    public function generate(Metrics $metrics)
    {
        if ($this->config->has('quiet')) {
            return;
        }

        $logFile = $this->config->get('report-summary-json');
        if (!$logFile) {
            return;
        }
        if (!file_exists(dirname($logFile)) || !is_writable(dirname($logFile))) {
            throw new \RuntimeException('You don\'t have permissions to write JSON report in ' . $logFile);
        }

        $summaryWriter = new SummaryWriter($metrics, new Consolidated($metrics), $this->config);
        file_put_contents($logFile, json_encode($summaryWriter->getReport(), JSON_PRETTY_PRINT));
    }
}
