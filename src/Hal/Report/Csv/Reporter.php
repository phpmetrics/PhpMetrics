<?php
declare(strict_types=1);

namespace Hal\Report\Csv;

use Hal\Application\Config\ConfigBagInterface;
use Hal\Component\Output\Output;
use Hal\Exception\NotWritableCsvReportException;
use Hal\Metric\ClassMetric;
use Hal\Metric\Metrics;
use Hal\Metric\Registry;
use Hal\Report\ReporterInterface;
use function array_map;
use function dirname;
use function fclose;
use function file_exists;
use function fopen;
use function fputcsv;
use function is_scalar;
use function is_writable;

/**
 * This class is responsible for the report on a CSV file.
 */
final class Reporter implements ReporterInterface
{
    public function __construct(
        private readonly ConfigBagInterface $config,
        private readonly Output $output
    ) {
    }

    /**
     * {@inheritDoc}
     */
    public function generate(Metrics $metrics): void
    {
        if ($this->output->isQuiet()) {
            return;
        }

        $logFile = $this->config->get('report-csv');
        if (!$logFile) {
            return;
        }
        if (!file_exists(dirname($logFile)) || !is_writable(dirname($logFile))) {
            throw NotWritableCsvReportException::noPermission($logFile);
        }

        $allMetricsNames = Registry::allForStructures();
        $csvHandler = fopen($logFile, 'wb');
        fputcsv($csvHandler, $allMetricsNames);
        array_map(function (ClassMetric $metric) use ($csvHandler, $allMetricsNames): void {
            fputcsv($csvHandler, $this->generateRowData($metric, $allMetricsNames));
        }, $metrics->getClassMetrics());
        fclose($csvHandler);
    }

    /**
     * Generates a list of metrics value for the given ClassMetric object, ready to be added in the CSV handler.
     *
     * @param ClassMetric $metric
     * @param array<int, string> $allMetricsNames
     * @return array<int, mixed>
     */
    private function generateRowData(ClassMetric $metric, array $allMetricsNames): array
    {
        return array_map(static function (string $key) use ($metric): string|int|bool|float {
            $value = $metric->get($key);
            return is_scalar($value) ? $value : 'N/A';
        }, $allMetricsNames);
    }
}
