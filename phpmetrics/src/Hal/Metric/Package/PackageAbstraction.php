<?php

namespace Hal\Metric\Package;

use Hal\Metric\MetricNullException;
use Hal\Metric\Metrics;
use Hal\Metric\PackageMetric;

class PackageAbstraction
{
    /** @return void */
    public function calculate(Metrics $metrics)
    {
        foreach ($metrics->all() as $eachPackage) {
            if (! $eachPackage instanceof PackageMetric) {
                continue;
            }
            $abstractClassCount = 0;
            $classCount = count($eachPackage->getClasses());
            foreach ($eachPackage->getClasses() as $eachClassName) {
                $eachClass = $metrics->get($eachClassName);
                if ($eachClass === null) {
                    throw new MetricNullException($eachClassName, self::class);
                }
                $abstractClassCount += $eachClass->get('abstract');
            }
            $eachPackage->setAbstraction($abstractClassCount / $classCount);
        }
    }
}
