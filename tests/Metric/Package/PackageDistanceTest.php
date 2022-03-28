<?php
declare(strict_types=1);

namespace Tests\Hal\Metric\Package;

use Generator;
use Hal\Metric\Metrics;
use Hal\Metric\Package\PackageDistance;
use Hal\Metric\PackageMetric;
use Phake;
use PHPUnit\Framework\TestCase;

final class PackageDistanceTest extends TestCase
{
    /**
     * @return Generator
     */
    public function providePackagesAndExpectedDistance(): Generator
    {
        $package = Phake::mock(PackageMetric::class);
        Phake::when($package)->__call('getAbstraction', [])->thenReturn(null);
        Phake::when($package)->__call('getInstability', [])->thenReturn(42);
        yield 'Abstraction is NULL' => [$package, null];

        $package = Phake::mock(PackageMetric::class);
        Phake::when($package)->__call('getAbstraction', [])->thenReturn(0);
        Phake::when($package)->__call('getInstability', [])->thenReturn(42);
        yield 'Abstraction is 0' => [$package, 41];

        $package = Phake::mock(PackageMetric::class);
        Phake::when($package)->__call('getAbstraction', [])->thenReturn(-3.5);
        Phake::when($package)->__call('getInstability', [])->thenReturn(9);
        yield 'Abstraction is negative' => [$package, 4.5];

        $package = Phake::mock(PackageMetric::class);
        Phake::when($package)->__call('getAbstraction', [])->thenReturn(3.5);
        Phake::when($package)->__call('getInstability', [])->thenReturn(9);
        yield 'Abstraction is positive' => [$package, 11.5];

        $package = Phake::mock(PackageMetric::class);
        Phake::when($package)->__call('getAbstraction', [])->thenReturn(0);
        Phake::when($package)->__call('getInstability', [])->thenReturn(null);
        yield 'Instability is NULL' => [$package, null];

        $package = Phake::mock(PackageMetric::class);
        Phake::when($package)->__call('getAbstraction', [])->thenReturn(42);
        Phake::when($package)->__call('getInstability', [])->thenReturn(0);
        yield 'Instability is 0' => [$package, 41];

        $package = Phake::mock(PackageMetric::class);
        Phake::when($package)->__call('getAbstraction', [])->thenReturn(9);
        Phake::when($package)->__call('getInstability', [])->thenReturn(-7.86);
        yield 'Instability is negative' => [$package, 0.14];

        $package = Phake::mock(PackageMetric::class);
        Phake::when($package)->__call('getAbstraction', [])->thenReturn(90.5);
        Phake::when($package)->__call('getInstability', [])->thenReturn(129.65);
        yield 'Instability is positive' => [$package, 219.15];

        $package = Phake::mock(PackageMetric::class);
        Phake::when($package)->__call('getAbstraction', [])->thenReturn(null);
        Phake::when($package)->__call('getInstability', [])->thenReturn(null);
        yield 'Abstraction and Instability are both NULL' => [$package, null];

        $package = Phake::mock(PackageMetric::class);
        Phake::when($package)->__call('getAbstraction', [])->thenReturn(0);
        Phake::when($package)->__call('getInstability', [])->thenReturn(0);
        yield 'Abstraction and Instability are both 0' => [$package, 1];

        $package = Phake::mock(PackageMetric::class);
        Phake::when($package)->__call('getAbstraction', [])->thenReturn(5000001);
        Phake::when($package)->__call('getInstability', [])->thenReturn(5000000);
        yield 'Abstraction is greater than Instability' => [$package, 10000000];

        $package = Phake::mock(PackageMetric::class);
        Phake::when($package)->__call('getAbstraction', [])->thenReturn(333);
        Phake::when($package)->__call('getInstability', [])->thenReturn(333);
        yield 'Abstraction is equal to Instability' => [$package, 665];

        $package = Phake::mock(PackageMetric::class);
        Phake::when($package)->__call('getAbstraction', [])->thenReturn(5000000);
        Phake::when($package)->__call('getInstability', [])->thenReturn(5000001);
        yield 'Abstraction is less than Instability' => [$package, 10000000];

        $package = Phake::mock(PackageMetric::class);
        Phake::when($package)->__call('getAbstraction', [])->thenReturn(23);
        Phake::when($package)->__call('getInstability', [])->thenReturn(20);
        yield 'A + I - 1 > 0' => [$package, 42];

        $package = Phake::mock(PackageMetric::class);
        Phake::when($package)->__call('getAbstraction', [])->thenReturn(4.6785);
        Phake::when($package)->__call('getInstability', [])->thenReturn(-3.6785);
        yield 'A + I - 1 = 0' => [$package, 0];

        $package = Phake::mock(PackageMetric::class);
        Phake::when($package)->__call('getAbstraction', [])->thenReturn(-9.1);
        Phake::when($package)->__call('getInstability', [])->thenReturn(-0.9);
        yield 'A + I - 1 < 0' => [$package, 11];
    }

    /**
     * @dataProvider providePackagesAndExpectedDistance
     * @param Phake\IMock&PackageMetric $package
     * @param float|null $expectedDistance
     * @return void
     */
    //#[DataProvider('providePackagesAndExpectedDistance')] TODO: PHPUnit 10.
    public function testPackageDistanceIsCalculable(
        Phake\IMock&PackageMetric $package,
        null|float $expectedDistance
    ): void {
        $metricsMock = Phake::mock(Metrics::class);
        Phake::when($metricsMock)->__call('getPackageMetrics', [])->thenReturn([$package]);

        (new PackageDistance($metricsMock))->calculate();

        Phake::verify($metricsMock)->__call('getPackageMetrics', []);
        Phake::verify($package, Phake::atLeast(1))->__call('getAbstraction', []); // At least the 1st check is done.
        Phake::verify($package, Phake::atLeast(0))->__call('getInstability', []); // Ignored if abstraction is null.
        if (null === $expectedDistance) {
            Phake::verify($package, Phake::never())->__call('setNormalizedDistance', [Phake::anyParameters()]);
        } else {
            Phake::verify($package)->__call('setNormalizedDistance', [$expectedDistance]);
        }
        Phake::verifyNoOtherInteractions($package);
        Phake::verifyNoOtherInteractions($metricsMock);
    }
}
