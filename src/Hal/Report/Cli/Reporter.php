<?php
declare(strict_types=1);

namespace Hal\Report\Cli;

use Hal\Component\Output\Output;
use Hal\Metric\Metrics;
use Hal\Report\ReporterInterface;
use Hal\Report\SummaryProviderInterface;

/**
 * This class is responsible for the report on CLI output.
 */
final class Reporter implements ReporterInterface
{
    public function __construct(
        private readonly SummaryProviderInterface $summary,
        private readonly Output $output
    ) {
    }

    /**
     * {@inheritDoc}
     */
    public function generate(Metrics $metrics): void
    {
        if (false === $this->summary->getReportFile()) {
            return;
        }

        $this->summary->summarize($metrics);
        /** @var string $report */
        $report = $this->summary->getReport();
        $this->output->write($report);
    }
}
