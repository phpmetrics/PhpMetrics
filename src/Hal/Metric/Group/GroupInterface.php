<?php
declare(strict_types=1);

namespace Hal\Metric\Group;

use Hal\Metric\Metrics;

/**
 * Allows grouping metrics by regex, given them a name.
 */
interface GroupInterface
{
    /**
     * @return non-empty-string
     */
    public function getRegex(): string;

    /**
     * @param Metrics $metrics
     * @return Metrics
     */
    public function reduceMetrics(Metrics $metrics): Metrics;
}
