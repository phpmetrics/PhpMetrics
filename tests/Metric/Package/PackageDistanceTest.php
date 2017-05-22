<?php

namespace Test\Hal\Metric\Package;

use Hal\Metric\ClassMetric;
use Hal\Metric\Metrics;
use Hal\Metric\Package\PackageDistance;
use Hal\Metric\PackageMetric;
use PHPUnit_Framework_TestCase;

/**
 * @group metric
 * @group package
 */
class PackageDistanceTest extends PHPUnit_Framework_TestCase
{
    /**
     * @dataProvider provideExamples
     * @param float|null $instability
     * @param float|null $abstraction
     * @param float|null $expectedDistance
     */
    public function testItCalculatesTheNormalizedDistanceOfAllPackages($instability, $abstraction, $expectedDistance)
    {
        $metrics = new Metrics();
        $metrics->attach(new ClassMetric('Ignored'));

        $package = new PackageMetric('MyPackage\\');
        $package->setInstability($instability);
        $package->setAbstraction($abstraction);

        $metrics->attach($package);

        (new PackageDistance())->calculate($metrics);

        $this->assertSame($expectedDistance, $package->getNormalizedDistance());
    }

    public static function provideExamples()
    {
        return [
            'missing instability and abstraction' => [null, null, null],
            'missing instability' => [null, 1.0, null],
            'missing abstraction' => [1.0, null, null],
            'abstract and stable' => [1.0, 0.0, 0.0],
            'concrete and instable' => [0.0, 1.0, 0.0],
            'abstract and instable' => [1.0, 1.0, 1.0],
            'concrete and stable' => [0.0, 0.0, 1.0],
        ];
    }
}
