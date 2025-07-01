<?php

namespace Hal\Metric\Package;

use Hal\Metric\Metrics;
use Hal\Metric\PackageMetric;

class PackageInstability
{
    public function calculate(Metrics $metrics)
    {
        /* @var $packages PackageMetric[] */
        $packages = array_filter($metrics->all(), function ($metric) {
            return $metric instanceof PackageMetric;
        });

        // Calculate instability
        $instabilitiesByPackage = [];
        foreach ($packages as $eachPackage) {
            $afferentCoupling = count($eachPackage->getIncomingClassDependencies());
            $efferentCoupling = count($eachPackage->getOutgoingClassDependencies());
            if ($afferentCoupling + $efferentCoupling !== 0) {
                $eachPackage->setInstability(
                    $efferentCoupling / ($afferentCoupling + $efferentCoupling)
                );
                $instabilitiesByPackage[$eachPackage->getName()] = $eachPackage->getInstability();
            }
        }
        // Set depending instabilities
        foreach ($packages as $eachPackage) {
            $dependentInstabilities = array_map(function ($packageName) use ($instabilitiesByPackage) {
                //return $instabilitiesByPackage[$packageName] ?? null;
                return isset($instabilitiesByPackage[$packageName]) ? $instabilitiesByPackage[$packageName] : null;
            }, $eachPackage->getOutgoingPackageDependencies());
            $dependentInstabilities = array_combine(
                $eachPackage->getOutgoingPackageDependencies(),
                $dependentInstabilities
            );
            $dependentInstabilities = array_filter($dependentInstabilities, 'is_float');
            $eachPackage->setDependentInstabilities($dependentInstabilities);
        }
    }
}
