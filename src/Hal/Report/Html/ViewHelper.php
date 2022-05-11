<?php
declare(strict_types=1);

namespace Hal\Report\Html;

use function array_column;
use function max;
use function min;
use function round;
use function sprintf;

/**
 * This class gives helper functions for the HTML report of the metrics values analyzed by PhpMetrics.
 */
final class ViewHelper
{
    /**
     * @var array<string, array{min: mixed, max: mixed}> $caches List of min-max values stored in cache by attribute.
     */
    private array $caches = [];

    /**
     * Returns a local percentile of a single value against others.
     * For instance, the value 7 will be placed at 70% compared to the values [0, 1, 4, 7, 10].
     *
     * @param array<array<string, mixed>> $list List of items which contains different metrics, all stored as key/value.
     * @param string $metricName Name of the metric to check for each element in the list.
     * @param mixed $currentValue Value that need to be compared among the others in the list.
     * @return float The local percentile of the value compare to other values, between 0 and 1 included.
     */
    private function getMetricPercentile(array $list, string $metricName, mixed $currentValue): float
    {
        if (!isset($this->caches[$metricName])) {
            $values = array_column($list, $metricName);
            $this->caches[$metricName] = ['max' => max(0, ...$values), 'min' => min(1, ...$values)];
        }

        ['max' => $max, 'min' => $min] = $this->caches[$metricName];
        $percent = (($currentValue - $min) * 100) / (max(1, $max - $min));
        return min(1, max(0, round($percent / 100, 2)));
    }

    /**
     * Style an element according its position in a range.
     *
     * @param array<array<string, mixed>> $list List of items which contains different metrics, all stored as key/value.
     * @param string $metricName Name of the metric to check for each element in the list.
     * @param mixed $currentValue Value that need to be compared among the others in the list.
     * @return string The HTML style attribute calculated.
     */
    public function gradientStyleFor(array $list, string $metricName, mixed $currentValue): string
    {
        return sprintf(
            ' style="background-color: hsla(203, 82%%, 76%%, %s);"',
            $this->getMetricPercentile($list, $metricName, $currentValue)
        );
    }
}
