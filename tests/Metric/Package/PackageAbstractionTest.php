<?php

namespace Test\Hal\Metric\Package;

use Hal\Metric\ClassMetric;
use Hal\Metric\Metric;
use Hal\Metric\Metrics;
use Hal\Metric\Package\PackageAbstraction;
use Hal\Metric\PackageMetric;
use PHPUnit_Framework_TestCase;

/**
 * @group metric
 * @group package
 */
class PackageAbstractionTest extends PHPUnit_Framework_TestCase
{
    public function testItCalculatesTheAbstractionOfEachPackage()
    {
        $metrics = $this->metricsOf([
            $this->aPackage('SemiAbstract\\', [
                $this->aClass('SemiAbstract\\Class_'),
                $this->anAbstractClass('SemiAbstract\\AbstractClass'),
            ]),
            $this->aPackage('Abstract\\', [
                $this->anAbstractClass('Abstract\\AbstractClass'),
            ]),
            $this->aPackage('Concrete\\', [
                $this->aClass('Stable\\Class_'),
            ])
        ]);

        $object = new PackageAbstraction();
        $object->calculate($metrics);

        $this->assertSame(0.5, $metrics->get('SemiAbstract\\')->getAbstraction());
        $this->assertSame(1.0, $metrics->get('Abstract\\')->getAbstraction());
        $this->assertSame(0.0, $metrics->get('Concrete\\')->getAbstraction());
    }

    /**
     * @param Metric[][] $metrics
     * @return Metrics
     */
    private function metricsOf(array $metrics)
    {
        $result = new Metrics();
        foreach ($metrics as $eachMetrics) {
            foreach ($eachMetrics as $eachMetric) {
                $result->attach($eachMetric);
            }
        }
        return $result;
    }

    /**
     * @param ClassMetric[] $classes
     * @return Metric[] $metrics
     */
    private function aPackage($packageName, array $classes)
    {
        $packageMetric = new PackageMetric($packageName);
        foreach ($classes as $each) {
            $packageMetric->addClass($each->getName());
        }
        $result = $classes;
        $result[] = $packageMetric;
        return $result;
    }

    /**
     * @param string $className
     * @return ClassMetric
     */
    private function anAbstractClass($className)
    {
        $metric = new ClassMetric($className);
        $metric->set('abstract', true);
        return $metric;
    }

    /**
     * @param string $className
     * @return ClassMetric
     */
    private function aClass($className)
    {
        $metric = new ClassMetric($className);
        $metric->set('abstract', false);
        return $metric;
    }
}
