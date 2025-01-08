<?php
declare(strict_types=1);

namespace Hal\Report;

use Hal\Metric\Metrics;

/**
 * This interface allows a SummaryProvider object to summarize given metrics for calculation, and report the results
 * into a dedicated format defined by the SummaryProvider itself.
 */
interface SummaryProviderInterface
{
    /**
     * Calculate the summary of the given metrics.
     *
     * @param Metrics $metrics
     * @return void
     */
    public function summarize(Metrics $metrics): void;

    /**
     * Return the report of the summary, into a type adapted for the report format:
     * - returns "string" for CLI or OpenMetrics
     * - returns "array" for JSON
     *
     * @return string|array<string, mixed>
     */
    public function getReport(): string|array;

    /**
     * If the return is a string, returns the location of where to report.
     * If TRUE, the report must be done (output to CLI, or a resource).
     * If FALSE, the report must not be done.
     *
     * @return string|bool
     */
    public function getReportFile(): string|bool;
}
