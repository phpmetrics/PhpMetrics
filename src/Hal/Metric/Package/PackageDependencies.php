<?php

namespace Hal\Metric\Package;

use Hal\Metric\ClassMetric;
use Hal\Metric\InterfaceMetric;
use Hal\Metric\Metric;
use Hal\Metric\Metrics;
use Hal\Metric\PackageMetric;

class PackageDependencies
{
    /** @return void */
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
     *
     * @return void
     */
    private function increaseDependencies(Metric $class, Metrics $metrics)
    {
        if (! $class->has('package') || ! $class->has('externals')) {
            return;
        }
        /** @var PackageMetric $incomingPackage */
        $incomingPackage = $metrics->get($class->get('package'));
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

    /**
     * @param string $className
     * @return string
     */
    private function getPackageOfClass($className, Metrics $metrics)
    {
        $metric = $metrics->get($className);
        if ($metric !== null && $metric->has('package')) {
            return $metric->get('package');
        }

        if (strpos($className, '\\') === false) {
            return '\\';
        }
        $parts = explode('\\', $className);
        array_pop($parts);
        return implode('\\', $parts) . '\\';
    }
}
