<?php
declare(strict_types=1);

namespace Hal\Metric\Package;

use Hal\Metric\CalculableInterface;
use Hal\Metric\Metrics;
use Hal\Metric\PackageMetric;
use function abs;
use function array_map;

/**
 * This class calculates the distance metric for each package.
 */
final class PackageDistance implements CalculableInterface
{
    public function __construct(private readonly Metrics $metrics)
    {
    }

    /**
     * {@inheritDoc}
     */
    public function calculate(): void
    {
        array_map(static function (PackageMetric $package): void {
            if (null === $package->getAbstraction() || null === $package->getInstability()) {
                return;
            }
            $package->setNormalizedDistance(abs($package->getAbstraction() + $package->getInstability() - 1));
        }, $this->metrics->getPackageMetrics());
    }
}
