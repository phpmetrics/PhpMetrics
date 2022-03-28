<?php
declare(strict_types=1);

namespace Tests\Hal\Metric;

use Hal\Metric\ClassMetric;
use Hal\Metric\InterfaceMetric;
use Hal\Metric\Metrics;
use Hal\Metric\PackageMetric;
use PHPUnit\Framework\TestCase;

final class MetricsTest extends TestCase
{
    public function testMetricsStorage(): void
    {
        $metrics = new Metrics();

        self::assertSame([], $metrics->all());
        self::assertSame([], $metrics->jsonSerialize());
        self::assertSame([], $metrics->getClassMetrics());
        self::assertSame([], $metrics->getInterfaceMetrics());
        self::assertSame([], $metrics->getPackageMetrics());

        $classMetric = new ClassMetric('Class_UnitTest');
        $interfaceMetric = new InterfaceMetric('Interface_UnitTest');
        $packageMetric = new PackageMetric('Package_UnitTest');

        self::assertFalse($metrics->has('Class_UnitTest'));
        self::assertFalse($metrics->has('Interface_UnitTest'));
        self::assertFalse($metrics->has('Package_UnitTest'));
        self::assertNull($metrics->get('Class_UnitTest'));
        self::assertNull($metrics->get('Interface_UnitTest'));
        self::assertNull($metrics->get('Package_UnitTest'));

        $metrics->attach($classMetric);
        $metrics->attach($interfaceMetric);
        $metrics->attach($packageMetric);

        self::assertTrue($metrics->has('Class_UnitTest'));
        self::assertTrue($metrics->has('Interface_UnitTest'));
        self::assertTrue($metrics->has('Package_UnitTest'));
        self::assertSame($classMetric, $metrics->get('Class_UnitTest'));
        self::assertSame($interfaceMetric, $metrics->get('Interface_UnitTest'));
        self::assertSame($packageMetric, $metrics->get('Package_UnitTest'));

        $expectedClassMetrics = ['Class_UnitTest' => $classMetric];
        $expectedInterfaceMetrics = ['Interface_UnitTest' => $interfaceMetric];
        $expectedPackageMetrics = ['Package_UnitTest' => $packageMetric];
        $expectedAll = [...$expectedClassMetrics, ...$expectedInterfaceMetrics, ...$expectedPackageMetrics];
        self::assertSame($expectedAll, $metrics->all());
        self::assertSame($expectedAll, $metrics->jsonSerialize());
        // Interfaces are also counted as classes.
        self::assertSame([...$expectedClassMetrics, ...$expectedInterfaceMetrics], $metrics->getClassMetrics());
        self::assertSame($expectedInterfaceMetrics, $metrics->getInterfaceMetrics());
        self::assertSame($expectedPackageMetrics, $metrics->getPackageMetrics());
    }
}
