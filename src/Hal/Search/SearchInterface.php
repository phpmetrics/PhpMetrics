<?php
declare(strict_types=1);

namespace Hal\Search;

use Hal\Metric\Metric;

/**
 * Represents a way to define how a criterion of search can match a metric.
 */
interface SearchInterface
{
    /**
     * @return string
     */
    public function getName(): string;

    /**
     * @return array<string, mixed>
     */
    public function getConfig(): array;

    /**
     * Returns true if the given metric matches the criterion of search. False otherwise.
     *
     * @param Metric $metric
     * @return bool
     */
    public function matches(Metric $metric): bool;
}
