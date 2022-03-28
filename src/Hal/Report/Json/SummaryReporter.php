<?php
declare(strict_types=1);

namespace Hal\Report\Json;

use Hal\Metric\Metrics;
use Hal\Report\ReporterInterface;
use Hal\Report\SummaryProviderInterface;
use JsonException;
use function file_put_contents;
use function json_encode;
use const JSON_PRETTY_PRINT;
use const JSON_THROW_ON_ERROR;

/**
 * This class is responsible for the report of the summary of te metrics on a JSON file.
 */
final class SummaryReporter implements ReporterInterface
{
    public function __construct(private readonly SummaryProviderInterface $summary)
    {
    }

    /**
     * {@inheritDoc}
     * @throws JsonException
     */
    public function generate(Metrics $metrics): void
    {
        $logFile = $this->summary->getReportFile();
        if (false === $logFile) {
            return;
        }
        $this->summary->summarize($metrics);
        file_put_contents($logFile, json_encode($this->summary->getReport(), JSON_THROW_ON_ERROR | JSON_PRETTY_PRINT));
    }
}
