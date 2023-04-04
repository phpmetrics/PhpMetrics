<?php
declare(strict_types=1);

namespace Hal\Report\Csv;

use Hal\Application\Config\ConfigBagInterface;
use Hal\Component\File\WriterInterface;
use Hal\Component\Output\Output;
use Hal\Exception\NotWritableCsvReportException;
use Hal\Metric\ClassMetric;
use Hal\Metric\Metrics;
use Hal\Metric\Registry;
use Hal\Report\ReporterInterface;
use function array_map;
use function dirname;
use function is_scalar;

/**
 * This class is responsible for the report on a CSV file.
 */
final class Reporter implements ReporterInterface
{
    public function __construct(
        private readonly ConfigBagInterface $config,
        private readonly Output $output,
        private readonly WriterInterface $fileWriter,
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

        /** @var null|string $logFile */
        $logFile = $this->config->get('report-csv');
        if (null === $logFile) {
            return;
        }
        if (!$this->fileWriter->exists(dirname($logFile)) || !$this->fileWriter->isWritable(dirname($logFile))) {
            throw NotWritableCsvReportException::noPermission($logFile);
        }

        $this->fileWriter->writeCsv(
            $logFile,
            array_map($this->generateRowData(...), $metrics->getClassMetrics()),
            Registry::allForStructures()
        );
    }

    /**
     * Generates a list of metrics value for the given ClassMetric object, ready to be added in the CSV handler.
     *
     * @param ClassMetric $metric
     * @return array<int, string|int|bool|float>
     */
    private function generateRowData(ClassMetric $metric): array
    {
        return array_map(static function (string $key) use ($metric): string|int|bool|float {
            $value = $metric->get($key);
            return is_scalar($value) ? $value : 'N/A';
        }, Registry::allForStructures());
    }
}
