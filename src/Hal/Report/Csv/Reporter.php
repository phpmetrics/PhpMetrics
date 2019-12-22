<?php
namespace Hal\Report\Csv;

use Hal\Application\Config\Config;
use Hal\Metric\ClassMetric;
use Hal\Metric\Metrics;
use Hal\Metric\Registry;
use Hal\Report\ReporterInterface;
use RuntimeException;

/**
 * This class takes care about the global report in CSV of consolidated metrics.
 */
class Reporter implements ReporterInterface
{
    /** @var Config */
    private $config;

    /**
     * @param Config $config
     */
    public function __construct(Config $config)
    {
        $this->config = $config;
    }

    /**
     * {@inheritDoc}
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

        $logDir = dirname($logFile);
        if (!file_exists($logDir) || !is_writable($logDir)) {
            throw new RuntimeException('You do not have permissions to write CSV report in ' . $logFile);
        }

        $availableMetrics = (new Registry())->allForStructures();
        $logPointer = fopen($logFile, 'wb');
        fputcsv($logPointer, $availableMetrics);

        foreach ($metrics->all() as $metric) {
            if (!$metric instanceof ClassMetric) {
                continue;
            }
            $row = array_map(static function ($key) use ($metric) {
                $data = $metric->get($key);
                return (!is_scalar($data)) ? 'N/A': $data;
            }, $availableMetrics);

            fputcsv($logPointer, $row);
        }
        fclose($logPointer);
    }
}
