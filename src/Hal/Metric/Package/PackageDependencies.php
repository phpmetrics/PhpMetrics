<?php

namespace Hal\Metric\Package;

use Hal\Metric\ClassMetric;
use Hal\Metric\InterfaceMetric;
use Hal\Metric\Metric;
use Hal\Metric\Metrics;
use Hal\Metric\PackageMetric;

class PackageDependencies
{
    public function calculate(Metrics $metrics)
    {
        $classes = array_filter($metrics->all(), function (Metric $metric) {
            return $metric instanceof ClassMetric || $metric instanceof InterfaceMetric;
        });

        foreach ($classes as $each) {
            $this->increaseDependencies($each, $metrics);
        }
    }

    /**
     * @param ClassMetric|InterfaceMetric|Metric $class
     * @param Metrics $metrics
     */
    private function increaseDependencies(Metric $class, Metrics $metrics)
    {
        if (! $class->has('package') || ! $class->has('externals')) {
            return;
        }
        $incomingPackage = $metrics->get($class->get('package')); /* @var $incomingPackage PackageMetric */
        foreach ($class->get('externals') as $outgoingClassName) {
            // same package?
            if (in_array($outgoingClassName, $incomingPackage->getClasses())) {
                continue;
            }
            $outgoingPackageName = $this->getPackageOfClass($outgoingClassName, $metrics);
            $incomingPackage->addOutgoingClassDependency($outgoingClassName, $outgoingPackageName);
            $outgoingPackage = $metrics->get($outgoingPackageName);

            if ($outgoingPackage instanceof PackageMetric) {
                $outgoingPackage->addIncomingClassDependency($class->getName(), $incomingPackage->getName());
            }
        }
    }

    private function getPackageOfClass($className, Metrics $metrics)
    {
        if ($metrics->has($className) && $metrics->get($className)->has('package')) {
            return $metrics->get($className)->get('package');
        }
        if (strpos($className, '\\') === false) {
            return '\\';
        }
        $parts = explode('\\', $className);
        array_pop($parts);
        return implode('\\', $parts) . '\\';
    }
}
