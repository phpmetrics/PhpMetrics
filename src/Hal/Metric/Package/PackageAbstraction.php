<?php

namespace Hal\Metric\Package;

use Hal\Metric\Metrics;
use Hal\Metric\PackageMetric;

class PackageAbstraction
{
    public function calculate(Metrics $metrics)
    {
        /* @var $packages PackageMetric[] */
        foreach ($metrics->all() as $eachPackage) {
            if (! $eachPackage instanceof PackageMetric) {
                continue;
            }
            $abstractClassCount = 0;
            $classCount = count($eachPackage->getClasses());
            foreach ($eachPackage->getClasses() as $eachClassName) {
                $eachClass = $metrics->get($eachClassName);
                $abstractClassCount += $eachClass->get('abstract');
            }
            $eachPackage->setAbstraction($abstractClassCount / $classCount);
        }
    }
}
