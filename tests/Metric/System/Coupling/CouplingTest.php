<?php
declare(strict_types=1);

namespace Tests\Hal\Metric\System\Coupling;

use Hal\Metric\ClassMetric;
use Hal\Metric\Metrics;
use Hal\Metric\System\Coupling\Coupling;
use Phake;
use PHPUnit\Framework\TestCase;
use function array_map;

final class CouplingTest extends TestCase
{
    public function testNoErrorsIfNoClassMetrics(): void
    {
        $metricsMock = Phake::mock(Metrics::class);
        Phake::when($metricsMock)->__call('getClassMetrics', [])->thenReturn([]);

        (new Coupling($metricsMock))->calculate();

        Phake::verify($metricsMock, Phake::times(2))->__call('getClassMetrics', []);
        Phake::verifyNoOtherInteractions($metricsMock);
    }

    public function testCouplingIsCalculable(): void
    {
        $metricsMock = Phake::mock(Metrics::class);
        $classMetrics = [
            Phake::mock(ClassMetric::class),
            Phake::mock(ClassMetric::class),
            Phake::mock(ClassMetric::class),
            Phake::mock(ClassMetric::class),
        ];
        array_map(static function (Phake\IMock&ClassMetric $classMetric, string $classMetricName): void {
            Phake::when($classMetric)->__call('get', ['name'])->thenReturn($classMetricName);
        }, $classMetrics, ['A', 'B', 'C', 'D']);
        Phake::when($classMetrics[0])->__call('get', ['externals'])->thenReturn([]);
        Phake::when($classMetrics[1])->__call('get', ['externals'])->thenReturn(['A', 'C']);
        Phake::when($classMetrics[2])->__call('get', ['externals'])->thenReturn(['A', 'B', 'C']);
        Phake::when($classMetrics[3])->__call('get', ['externals'])->thenReturn([]);

        Phake::when($metricsMock)->__call('getClassMetrics', [])->thenReturn($classMetrics);

        (new Coupling($metricsMock))->calculate();

        Phake::verify($classMetrics[0])->__call('set', ['afferentCoupling', 2]);
        Phake::verify($classMetrics[0])->__call('set', ['efferentCoupling', 0]);
        Phake::verify($classMetrics[0])->__call('set', ['instability', 0]);
        Phake::verify($classMetrics[1])->__call('set', ['afferentCoupling', 1]);
        Phake::verify($classMetrics[1])->__call('set', ['efferentCoupling', 2]);
        Phake::verify($classMetrics[1])->__call('set', ['instability', 0.67]);
        Phake::verify($classMetrics[2])->__call('set', ['afferentCoupling', 3]);
        Phake::verify($classMetrics[2])->__call('set', ['efferentCoupling', 4]);
        Phake::verify($classMetrics[2])->__call('set', ['instability', 0.57]);
        Phake::verify($classMetrics[3])->__call('set', ['afferentCoupling', 0]);
        Phake::verify($classMetrics[3])->__call('set', ['efferentCoupling', 0]);
        Phake::verify($classMetrics[3])->__call('set', ['instability', 0]);

        array_map(static function (Phake\IMock&ClassMetric $classMetric): void {
            Phake::verify($classMetric, Phake::times(2))->__call('get', ['name']);
            Phake::verify($classMetric)->__call('get', ['externals']);
            Phake::verifyNoOtherInteractions($classMetric);
        }, $classMetrics);
        Phake::verify($metricsMock, Phake::times(2))->__call('getClassMetrics', []);
        Phake::verifyNoOtherInteractions($metricsMock);
    }
}
