<?php

namespace Test\Hal\Metric\Package;

use Hal\Metric\ClassMetric;
use Hal\Metric\Metric;
use Hal\Metric\Metrics;
use Hal\Metric\Package\PackageAbstraction;
use Hal\Metric\PackageMetric;
use PHPUnit\Framework\TestCase;

/**
 * @group metric
 * @group package
 */
class PackageAbstractionTest extends TestCase
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

        /** @var PackageMetric */
        $metric = $metrics->get('SemiAbstract\\');
        $this->assertNotNull($metric);
        $this->assertSame(0.5, $metric->getAbstraction());

        /** @var PackageMetric */
        $metric = $metrics->get('Abstract\\');
        $this->assertNotNull($metric);
        $this->assertSame(1.0, $metric->getAbstraction());

        /** @var PackageMetric */
        $metric = $metrics->get('Concrete\\');
        $this->assertNotNull($metric);
        $this->assertSame(0.0, $metric->getAbstraction());
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
     * @param string $packageName
     * @param ClassMetric[] $classes
     *
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
