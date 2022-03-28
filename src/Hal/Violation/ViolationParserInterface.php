<?php
declare(strict_types=1);

namespace Hal\Violation;

use Hal\Metric\Metrics;

/**
 * Provides rules to support the application of a list of violations to all given metrics.
 */
interface ViolationParserInterface
{
    /**
     * Applies a list of violations to all given metrics.
     *
     * @param Metrics $metrics
     * @return void
     */
    public function apply(Metrics $metrics): void;
}
