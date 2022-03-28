<?php
declare(strict_types=1);

namespace Tests\Hal\Metric\System\Coupling;

use Hal\Metric\ClassMetric;
use Hal\Metric\Metrics;
use Hal\Metric\System\Coupling\PageRank;
use Phake;
use PHPUnit\Framework\TestCase;
use function array_map;

final class PageRankTest extends TestCase
{
    public function testCalculationOfPageRankWithoutClassMetrics(): void
    {
        $metricsMock = Phake::mock(Metrics::class);
        Phake::when($metricsMock)->__call('getClassMetrics', [])->thenReturn([]);

        (new PageRank($metricsMock))->calculate();

        Phake::verify($metricsMock)->__call('getClassMetrics', []);
        Phake::verifyNoOtherInteractions($metricsMock);
    }

    public function testCalculationOfPageRank(): void
    {
        $metricsMock = Phake::mock(Metrics::class);
        $classMetrics = [
            Phake::mock(ClassMetric::class),
            Phake::mock(ClassMetric::class),
            Phake::mock(ClassMetric::class),
            Phake::mock(ClassMetric::class),
        ];
        array_map(
            static function (Phake\IMock&ClassMetric $classMetric, string $classMetricName) use ($metricsMock): void {
                Phake::when($classMetric)->__call('get', ['name'])->thenReturn($classMetricName);
                Phake::when($metricsMock)->__call('get', [$classMetricName])->thenReturn($classMetric);
            },
            $classMetrics,
            ['A', 'B', 'C', 'D']
        );
        Phake::when($classMetrics[0])->__call('get', ['externals'])->thenReturn([]);
        Phake::when($classMetrics[1])->__call('get', ['externals'])->thenReturn(['A', 'C']);
        Phake::when($classMetrics[2])->__call('get', ['externals'])->thenReturn(['A', 'B', 'C']);
        Phake::when($classMetrics[3])->__call('get', ['externals'])->thenReturn([]);
        Phake::when($metricsMock)->__call('getClassMetrics', [])->thenReturn($classMetrics);

        (new PageRank($metricsMock))->calculate();

        Phake::verify($classMetrics[0])->__call('set', ['pageRank', 0.36]);
        Phake::verify($classMetrics[1])->__call('set', ['pageRank', 0.22]);
        Phake::verify($classMetrics[2])->__call('set', ['pageRank', 0.36]);
        Phake::verify($classMetrics[3])->__call('set', ['pageRank', 0.06]);

        array_map(static function (Phake\IMock&ClassMetric $classMetric): void {
            Phake::verify($classMetric)->__call('get', ['name']);
            Phake::verify($classMetric)->__call('get', ['externals']);
            Phake::verifyNoOtherInteractions($classMetric);
        }, $classMetrics);
        foreach (['A', 'B', 'C', 'D'] as $classMetricName) {
            Phake::verify($metricsMock)->__call('get', [$classMetricName]);
        }
        Phake::verify($metricsMock)->__call('getClassMetrics', []);
        Phake::verifyNoOtherInteractions($metricsMock);
    }
}
