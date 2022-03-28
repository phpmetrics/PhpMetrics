<?php
declare(strict_types=1);

namespace Hal\Metric;

/**
 * Interface Metric
 * Defines a metrics bag.
 */
interface Metric
{
    /**
     * Returns the ame of the metric.
     *
     * @return string
     */
    public function getName(): string;

    /**
     * Get a value from the current metric bag.
     * Returns NULL if the requested value is not present in the metric bag.
     *
     * @param string $key
     * @return mixed
     */
    public function get(string $key): mixed;

    /**
     * Set a value in the metric bag.
     *
     * @param string $key
     * @param mixed $value
     */
    public function set(string $key, mixed $value): void;

    /**
     * Check if the requested value is in the metric bag.
     *
     * @param string $key
     * @return bool
     */
    public function has(string $key): bool;

    /**
     * Returns all metric bag content.
     *
     * @return array<string, mixed>
     */
    public function all(): array;
}
