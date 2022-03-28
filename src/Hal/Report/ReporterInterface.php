<?php
declare(strict_types=1);

namespace Hal\Report;

use Hal\Metric\Metrics;

/**
 * Defines rules explaining how to generate a report.
 */
interface ReporterInterface
{
    /**
     * Generates a report using the metrics given in argument.
     *
     * @param Metrics $metrics
     * @return void
     */
    public function generate(Metrics $metrics): void;
}
