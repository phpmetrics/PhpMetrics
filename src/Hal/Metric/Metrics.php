<?php
declare(strict_types=1);

namespace Hal\Metric;

use JsonSerializable;
use function array_filter;
use function array_key_exists;

/**
 * Host all Metric objects in a single object.
 */
class Metrics implements JsonSerializable
{
    /** @var array<string, Metric> */
    private array $data = [];

    /**
     * @param Metric $metric
     */
    public function attach(Metric $metric): void
    {
        $this->data[$metric->getName()] = $metric;
    }

    /**
     * @param string $key
     * @return Metric|null
     */
    public function get(string $key): null|Metric
    {
        return $this->has($key) ? $this->data[$key] : null;
    }

    /**
     * @param string $key
     * @return bool
     */
    public function has(string $key): bool
    {
        return array_key_exists($key, $this->data);
    }

    /**
     * @return array<string, Metric>
     */
    public function all(): array
    {
        return $this->data;
    }

    /**
     * Returns only metrics that are class related.
     *
     * @return array<string, ClassMetric>
     */
    public function getClassMetrics(): array
    {
        return array_filter($this->data, static fn (Metric $metric): bool => $metric instanceof ClassMetric);
    }

    /**
     * Returns only metrics that are interface related.
     *
     * @return array<string, InterfaceMetric>
     */
    public function getInterfaceMetrics(): array
    {
        return array_filter($this->data, static fn (Metric $metric): bool => $metric instanceof InterfaceMetric);
    }

    /**
     * Returns only metrics that are package related.
     *
     * @return array<string, PackageMetric>
     */
    public function getPackageMetrics(): array
    {
        return array_filter($this->data, static fn (Metric $metric): bool => $metric instanceof PackageMetric);
    }

    /**
     * {@inheritDoc}
     * @return array<string, Metric>
     */
    public function jsonSerialize(): array
    {
        return $this->all();
    }
}
