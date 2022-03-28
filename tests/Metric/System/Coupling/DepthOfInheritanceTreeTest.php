<?php
declare(strict_types=1);

namespace Tests\Hal\Metric\System\Coupling;

use Generator;
use Hal\Exception\GraphException\NoSizeForCyclicGraphException;
use Hal\Metric\ClassMetric;
use Hal\Metric\Metrics;
use Hal\Metric\ProjectMetric;
use Hal\Metric\System\Coupling\DepthOfInheritanceTree;
use Phake;
use PHPUnit\Framework\TestCase;
use function array_map;

final class DepthOfInheritanceTreeTest extends TestCase
{
    public function provideClassMetricsForDifferentTrees(): Generator
    {
        yield 'No ClassMetrics' => [[], 0];

        $classMetrics = [
            Phake::mock(ClassMetric::class),
            Phake::mock(ClassMetric::class),
        ];
        array_map(static function (Phake\IMock&ClassMetric $classMetric, string $classMetricName): void {
            Phake::when($classMetric)->__call('get', ['name'])->thenReturn($classMetricName);
        }, $classMetrics, ['A', 'B']);
        Phake::when($classMetrics[0])->__call('get', ['parents'])->thenReturn(['B']);
        Phake::when($classMetrics[1])->__call('get', ['parents'])->thenReturn(['A']);
        yield 'ClassMetrics building a cyclic graph' => [$classMetrics, null];

        $classMetrics = [
            Phake::mock(ClassMetric::class),
            Phake::mock(ClassMetric::class),
            Phake::mock(ClassMetric::class),
            Phake::mock(ClassMetric::class),
        ];
        array_map(static function (Phake\IMock&ClassMetric $classMetric, string $classMetricName): void {
            Phake::when($classMetric)->__call('get', ['name'])->thenReturn($classMetricName);
        }, $classMetrics, ['A', 'B', 'C', 'D']);
        Phake::when($classMetrics[0])->__call('get', ['parents'])->thenReturn([]);
        Phake::when($classMetrics[1])->__call('get', ['parents'])->thenReturn(['A', 'C']);
        Phake::when($classMetrics[2])->__call('get', ['parents'])->thenReturn(['A']);
        Phake::when($classMetrics[3])->__call('get', ['parents'])->thenReturn(['C']);
        yield 'ClassMetrics building a calculable Depth of Inheritance' => [$classMetrics, 3];
    }

    /**
     * @dataProvider provideClassMetricsForDifferentTrees
     * @param array<Phake\IMock&ClassMetric> $classMetrics
     * @param float|null $expectedDepth
     * @return void
     */
    //#[DataProvider('provideClassMetricsForDifferentTrees')] TODO: PHPUnit 10
    public function testCalculationOfDepthOfInheritanceTree(array $classMetrics, null|float $expectedDepth): void
    {
        $metricsMock = Phake::mock(Metrics::class);
        Phake::when($metricsMock)->__call('getClassMetrics', [])->thenReturn($classMetrics);
        $projectMetricCollector = null;
        Phake::when($metricsMock)->__call('attach', [Phake::anyParameters()])->thenReturnCallback(
            static function (ProjectMetric $projectMetric) use (&$projectMetricCollector): void {
                $projectMetricCollector = $projectMetric;
            }
        );

        // If no expectedDepth, that's because of cyclic graph exception.
        if (null === $expectedDepth) {
            $this->expectExceptionObject(NoSizeForCyclicGraphException::incalculableSize());
        }

        (new DepthOfInheritanceTree($metricsMock))->calculate();

        if (null !== $expectedDepth) {
            self::assertInstanceOf(ProjectMetric::class, $projectMetricCollector);
            self::assertSame('tree', $projectMetricCollector->getName());
            self::assertTrue($projectMetricCollector->has('depthOfInheritanceTree'));
            self::assertSame($expectedDepth, $projectMetricCollector->get('depthOfInheritanceTree'));
        }

        array_map(static function (Phake\IMock&ClassMetric $classMetric): void {
            Phake::verify($classMetric)->__call('get', ['name']);
            Phake::verify($classMetric)->__call('get', ['parents']);
            Phake::verifyNoOtherInteractions($classMetric);
        }, $classMetrics);
        Phake::verify($metricsMock)->__call('getClassMetrics', []);
        Phake::verify($metricsMock)->__call('attach', [$projectMetricCollector]);
        Phake::verifyNoOtherInteractions($metricsMock);
    }
}
