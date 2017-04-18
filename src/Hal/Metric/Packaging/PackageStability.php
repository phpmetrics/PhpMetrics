<?php

namespace Hal\Metric\Packaging;

use Hal\Metric\Metrics;
use Hal\Metric\PackageMetric;

class PackageStability
{
    public function calculate(Metrics $metrics)
    {
        /* @var $packages PackageMetric[] */
        foreach ($metrics->all() as $eachPackage) {
            if (! $eachPackage instanceof PackageMetric) {
                continue;
            }
            $afferentCoupling = count($eachPackage->getIncomingClassDependencies());
            $efferentCoupling = count($eachPackage->getOutgoingClassDependencies());
            if ($afferentCoupling + $efferentCoupling !== 0) {
                $eachPackage->setInstability(
                    $efferentCoupling / ($afferentCoupling + $efferentCoupling)
                );
            }
        }
    }
}
