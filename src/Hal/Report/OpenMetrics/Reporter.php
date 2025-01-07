<?php
declare(strict_types=1);

namespace Hal\Report\OpenMetrics;

use Hal\Component\File\WriterInterface;
use Hal\Metric\Metrics;
use Hal\Report\ReporterInterface;
use Hal\Report\SummaryProviderInterface;

/**
 * This class is responsible for the OpenMetrics report on a file.
 */
final class Reporter implements ReporterInterface
{
    public function __construct(
        private readonly SummaryProviderInterface $summary,
        private readonly WriterInterface $fileWriter,
    ) {
    }

    /**
     * {@inheritDoc}
     */
    public function generate(Metrics $metrics): void
    {
        /** @var string|false $logFile */
        $logFile = $this->summary->getReportFile();
        if (false === $logFile) {
            return;
        }

        $this->summary->summarize($metrics);
        /** @var string $report */
        $report = $this->summary->getReport();
        $this->fileWriter->write($logFile, $report);
    }
}
