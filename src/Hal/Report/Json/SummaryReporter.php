<?php
declare(strict_types=1);

namespace Hal\Report\Json;

use Hal\Component\File\WriterInterface;
use Hal\Metric\Metrics;
use Hal\Report\ReporterInterface;
use Hal\Report\SummaryProviderInterface;

/**
 * This class is responsible for the report of the summary of te metrics on a JSON file.
 */
final class SummaryReporter implements ReporterInterface
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
        $this->fileWriter->writePrettyJson($logFile, $this->summary->getReport());
    }
}
