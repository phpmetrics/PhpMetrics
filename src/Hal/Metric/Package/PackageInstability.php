<?php
declare(strict_types=1);

namespace Hal\Metric\Package;

use Hal\Metric\CalculableInterface;
use Hal\Metric\Metrics;
use Hal\Metric\PackageMetric;
use function array_filter;
use function array_map;
use function is_float;

/**
 * This class is calculating the instability of each package using the afferent and efferent coupling of each package.
 */
final class PackageInstability implements CalculableInterface
{
    /** @var array<string, float> */
    private array $instabilitiesByPackage;

    public function __construct(private readonly Metrics $metrics)
    {
    }

    /**
     * {@inheritDoc}
     */
    public function calculate(): void
    {
        $packages = $this->metrics->getPackageMetrics();

        // Calculate instability
        $this->instabilitiesByPackage = [];
        array_map($this->setInstabilitiesByPackage(...), $packages);
        array_map($this->setDependentInstabilitiesByPackage(...), $packages);
    }

    /**
     * Set the package instability value.
     *
     * @param PackageMetric $package
     * @return void
     */
    private function setInstabilitiesByPackage(PackageMetric $package): void
    {
        $afferentCoupling = count($package->getIncomingClassDependencies());
        $efferentCoupling = count($package->getOutgoingClassDependencies());
        if (0 !== $afferentCoupling + $efferentCoupling) {
            $package->setInstability($efferentCoupling / ($afferentCoupling + $efferentCoupling));
            $this->instabilitiesByPackage[$package->getName()] = $package->getInstability();
        }
    }

    /**
     * Set the related instabilities for the given package.
     *
     * @param PackageMetric $package
     * @return void
     */
    private function setDependentInstabilitiesByPackage(PackageMetric $package): void
    {
        $dependentInstabilities = [];
        array_map(function (string $packageName) use (&$dependentInstabilities): void {
            $dependentInstabilities[$packageName] = $this->instabilitiesByPackage[$packageName] ?? null;
        }, $package->getOutgoingPackageDependencies());

        $package->setDependentInstabilities(array_filter($dependentInstabilities, is_float(...)));
    }
}
