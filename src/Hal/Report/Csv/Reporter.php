<?php

namespace Hal\Report\Csv;

use Hal\Application\Config\Config;
use Hal\Component\Output\Output;
use Hal\Metric\ClassMetric;
use Hal\Metric\Metrics;
use Hal\Metric\Registry;

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

        $logFile = $this->config->get('report-csv');
        if (!$logFile) {
            return;
        }
        if (!file_exists(dirname($logFile)) || !is_writable(dirname($logFile))) {
            throw new \RuntimeException('You don\'t have permissions to write CSV report in ' . $logFile);
        }

        $availables = (new Registry())->allForStructures();
        $hwnd = fopen($logFile, 'w');
        fputcsv($hwnd, $availables);

        foreach ($metrics->all() as $metric) {
            if (!$metric instanceof ClassMetric) {
                continue;
            }
            $row = [];
            foreach ($availables as $key) {
                $data = $metric->get($key);
                if (is_array($data) || !is_scalar($data)) {
                    $data = 'N/A';
                }

                array_push($row, $data);
            }
            fputcsv($hwnd, $row);
        }

        fclose($hwnd);
    }
}
