<?php
declare(strict_types=1);

namespace Tests\Hal\Metric\Package;

use Generator;
use Hal\Metric\Metric;
use Hal\Metric\Metrics;
use Hal\Metric\Package\PackageAbstraction;
use Hal\Metric\PackageMetric;
use Phake;
use PHPUnit\Framework\TestCase;
use function array_map;
use function explode;

final class PackageAbstractionTest extends TestCase
{
    public function testMetricWithoutPackagesIsCalculable(): void
    {
        $metricsMock = Phake::mock(Metrics::class);
        Phake::when($metricsMock)->__call('getPackageMetrics', [])->thenReturn([]);

        (new PackageAbstraction($metricsMock))->calculate();

        Phake::verify($metricsMock)->__call('getPackageMetrics', []);
        Phake::verifyNoOtherInteractions($metricsMock);
    }

    /**
     * @return Generator<string, array{0: array<PackageMetric>, 1: array<null|float>}>
     */
    public function providePackagesLists(): Generator
    {
        $packages = [Phake::mock(PackageMetric::class)];
        Phake::when($packages[0])->__call('getClasses', [])->thenReturn([]);
        $expected = [null];
        yield 'Single package, no classes' => [$packages, $expected];

        $packages = [
            Phake::mock(PackageMetric::class), // All classes inside are abstract
            Phake::mock(PackageMetric::class), // All classes inside are concrete
            Phake::mock(PackageMetric::class), // Mix of abstract and concrete classes
        ];
        Phake::when($packages[0])->__call('getClasses', [])->thenReturn(['Abstract-A', 'Abstract-B', 'Abstract-C']);
        Phake::when($packages[1])->__call('getClasses', [])->thenReturn(['Concrete-D', 'Concrete-E', 'Concrete-F']);
        Phake::when($packages[2])->__call('getClasses', [])->thenReturn(['Abstract-G', 'NULL-H', 'Concrete-I']);
        $expected = [1, 0, 1 / 3];
        yield '3 packages: 1 all abstract classes, 1 all concrete classes, 1 mixed' => [$packages, $expected];
    }

    /**
     * @dataProvider providePackagesLists
     * @param array<PackageMetric> $packages
     * @param array<null|float> $expectedPackagesAbstraction
     * @return void
     */
    //#[DataProvider('providePackagesLists')] TODO: PHPUnit 10
    public function testMetricWithPackagesIsCalculable(array $packages, array $expectedPackagesAbstraction): void
    {
        $metricsMock = Phake::mock(Metrics::class);
        Phake::when($metricsMock)->__call('getPackageMetrics', [])->thenReturn($packages);

        $allClassNames = [];

        Phake::when($metricsMock)->__call('get', [Phake::anyParameters()])->thenReturnCallback(
            static function (string $className) use (&$allClassNames): null|Metric {
                [$type, ] = explode('-', $className);
                $allClassNames[] = $className;
                if ('NULL' === $type) {
                    return null;
                }

                $classMetric = Phake::mock(Metric::class);
                Phake::when($classMetric)->__call('get', ['abstract'])->thenReturn('Abstract' === $type);
                return $classMetric;
            }
        );

        (new PackageAbstraction($metricsMock))->calculate();

        array_map(static function (Phake\IMock $packageMetric, null|float $expectedAbstraction): void {
            Phake::verify($packageMetric)->__call('getClasses', []);
            if (null !== $expectedAbstraction) {
                Phake::verify($packageMetric)->__call('setAbstraction', [$expectedAbstraction]);
            }
            Phake::verifyNoOtherInteractions($packageMetric);
        }, $packages, $expectedPackagesAbstraction);

        Phake::verify($metricsMock)->__call('getPackageMetrics', []);
        foreach ($allClassNames as $className) {
            Phake::verify($metricsMock)->__call('get', [$className]);
        }
        Phake::verifyNoOtherInteractions($metricsMock);
    }
}
