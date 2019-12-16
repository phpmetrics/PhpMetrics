<?php
namespace Hal\Report\Json;

use Hal\Application\Config\Config;
use Hal\Component\Output\Output;
use Hal\Metric\Metrics;

class Reporter
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

        $logFile = $this->config->get('report-json');
        if (!$logFile) {
            return;
        }
        if (!file_exists(dirname($logFile)) || !is_writable(dirname($logFile))) {
            throw new \RuntimeException('You don\'t have permissions to write JSON report in ' . $logFile);
        }

        file_put_contents($logFile, json_encode($metrics, JSON_PRETTY_PRINT));
    }
}
