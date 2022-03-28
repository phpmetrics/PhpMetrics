<?php
declare(strict_types=1);

namespace Hal\Metric\Package;

use Hal\Metric\CalculableInterface;
use Hal\Metric\Metrics;
use Hal\Metric\PackageMetric;
use function array_filter;
use function array_map;

/**
 * This class calculates the abstraction of packages based on abstraction metric of each class in a given package.
 */
final class PackageAbstraction implements CalculableInterface
{
    public function __construct(private readonly Metrics $metrics)
    {
    }

    /**
     * {@inheritDoc}
     */
    public function calculate(): void
    {
        array_map(function (PackageMetric $package): void {
            $classesInPackage = $package->getClasses();
            if ([] === $classesInPackage) {
                return;
            }
            $abstractClassesInPackage = array_filter($classesInPackage, $this->isAbstract(...));
            $package->setAbstraction(count($abstractClassesInPackage) / count($classesInPackage));
        }, $this->metrics->getPackageMetrics());
    }

    /**
     * Returns TRUE if, the given classname, a metric "abstract" is set to TRUE. FALSE otherwise.
     *
     * @param string $classname
     * @return bool
     */
    private function isAbstract(string $classname): bool
    {
        return (bool)$this->metrics->get($classname)?->get('abstract');
    }
}
