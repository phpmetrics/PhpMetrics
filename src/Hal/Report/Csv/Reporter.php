<?php
namespace Hal\Report\Csv;

use Hal\Application\Config\Config;
use Hal\Component\Output\Output;
use Hal\Metric\ClassMetric;
use Hal\Metric\Metrics;
use Hal\Metric\Registry;

/**
 * Class Reporter
 *
 * @package Hal\Report\Csv
 */
class Reporter
{

    /**
     * @var Config
     */
    private $config;

    /**
     * @var OutputInterface
     */
    private $output;

    /**
     * Reporter constructor.
     *
     * @param Config $config
     * @param Output|OutputInterface $output
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
        if ($this->config->has('quiet')) {
            return;
        }


        $logFile = $this->config->get('report-csv');
        if (!$logFile) {
            return;
        }
        if (!\file_exists(\dirname($logFile)) || !\is_writable(\dirname($logFile))) {
            throw new \RuntimeException('You don\'t have permissions to write CSV report in ' . $logFile);
        }

        $availables = (new Registry())->allForStructures();
        $hwnd = \fopen($logFile, 'wb');
        \fputcsv($hwnd, $availables);

        foreach ($metrics->all() as $metric) {
            if (!$metric instanceof ClassMetric) {
                continue;
            }
            $row = [];
            foreach ($availables as $key) {
                $data = $metric->get($key);
                if (\is_array($data) || !\is_scalar($data)) {
                    $data = 'N/A';
                }

                $row[] = $data;
            }
            \fputcsv($hwnd, $row);
        }

        \fclose($hwnd);
    }
}
