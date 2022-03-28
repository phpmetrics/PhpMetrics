<?php
declare(strict_types=1);

namespace Hal\Application;

use Hal\Metric\Metrics;

/**
 * Represents rules any ReporterHandler must provide to ensure its reporters can generate a report, under the conditions
 * expressed in the concrete implementation of this interface.
 */
interface ReporterHandlerInterface
{
    /**
     * Reports the given metrics for all reporters assigned in the handler.
     *
     * @param Metrics $metrics
     * @return void
     */
    public function report(Metrics $metrics): void;
}
