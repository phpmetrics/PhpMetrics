<?php
namespace Hal\Report\Csv;

use Hal\Application\Config\Config;
use Hal\Metric\ClassMetric;
use Hal\Metric\Metrics;
use Hal\Metric\Registry;
use Hal\Report\ReporterInterface;
use RuntimeException;
use function array_map;
use function dirname;
use function fclose;
use function file_exists;
use function fopen;
use function fputcsv;
use function is_scalar;
use function is_writable;

/**
 * This class takes care about the global report in CSV of consolidated metrics.
 */
final class Reporter implements ReporterInterface
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
