<?php
declare(strict_types=1);

namespace Tests\Hal\Metric\Class_\Component;

use Generator;
use Hal\Metric\Class_\Complexity\CyclomaticComplexityVisitor;
use Hal\Metric\Class_\Component\MaintainabilityIndexVisitor;
use Hal\Metric\Class_\Text\HalsteadVisitor;
use Hal\Metric\Class_\Text\LengthVisitor;
use Hal\Metric\Helper\MetricNameGenerator;
use Hal\Metric\Metric;
use Hal\Metric\Metrics;
use LogicException;
use Phake;
use PhpParser\Node;
use PHPUnit\Framework\TestCase;
use function array_keys;

/**
 * @phpstan-type ExternalMetrics array{lloc: int, cloc: int, loc: int, ccn: int, volume: float}
 * @phpstan-type ExpectedMetrics array{mi: int, mIwoC: int, commentWeight: int}
 */
final class MaintainabilityIndexVisitorTest extends TestCase
{
    /**
     * @return Generator<string, array{0: Node\Stmt\Class_|Node\Stmt\Trait_, 1: ExternalMetrics, 2: ExpectedMetrics}>
     */
    public function provideMetricsToCalculateMaintainabilityIndex(): Generator
    {
        $allowedNodeClasses = [
            'class' => Node\Stmt\Class_::class,
            'trait' => Node\Stmt\Trait_::class
        ];

        foreach ($allowedNodeClasses as $kind => $allowedNodeClass) {
            $node = Phake::mock($allowedNodeClass);

            $externalMetrics = ['lloc' => 300, 'cloc' => 143, 'loc' => 448, 'ccn' => 7, 'volume' => 223.2];
            $expected = ['mi' => 66.96, 'mIwoC' => 28.58, 'commentWeight' => 38.39];
            yield 'Average ' . $kind . ' metrics' => [$node, $externalMetrics, $expected];

            $externalMetrics = ['lloc' => 100000, 'cloc' => 4112, 'loc' => 1000, 'ccn' => 8000, 'volume' => 16000000];
            $expected = ['mi' => 0.01, 'mIwoC' => 0, 'commentWeight' => 0.01];
            yield 'So complex ' . $kind . ' than mIwoC should be negative' => [$node, $externalMetrics, $expected];

            $externalMetrics = ['lloc' => 1, 'cloc' => 102808379, 'loc' => 100000000, 'ccn' => 1, 'volume' => 0];
            $expected = ['mi' => 221, 'mIwoC' => 171, 'commentWeight' => 50];
            yield 'So small ' . $kind . ' than mIwoC is infinite' => [$node, $externalMetrics, $expected];

            $externalMetrics = ['lloc' => 1, 'cloc' => 0, 'loc' => 0, 'ccn' => 1, 'volume' => 1];
            $expected = ['mi' => 99.87, 'mIwoC' => 99.87, 'commentWeight' => 0];
            yield 'No lines of code in ' . $kind . ' so commentWeight = 0' => [$node, $externalMetrics, $expected];
        }
    }

    /**
     * @dataProvider provideMetricsToCalculateMaintainabilityIndex
     * @param Node\Stmt\Class_|Node\Stmt\Trait_ $node
     * @param ExternalMetrics $externalMetrics
     * @param ExpectedMetrics $expected
     * @return void
     */
    //#[DataProvider('provideMetricsToCalculateMaintainabilityIndex')] TODO: PHPUnit 10.
    public function testICanCalculateMaintainabilityIndexFromNode(
        Node\Stmt\Class_|Node\Stmt\Trait_ $node,
        array $externalMetrics,
        array $expected
    ): void {
        $node->namespacedName = Phake::mock(Node\Identifier::class);
        Phake::when($node->namespacedName)->__call('toString', [])->thenReturn('UnitTest@Node');
        $metricsMock = Phake::mock(Metrics::class);
        $classMetricMock = Phake::mock(Metric::class);
        $nodeName = MetricNameGenerator::getClassName($node);

        Phake::when($metricsMock)->__call('get', [$nodeName])->thenReturn($classMetricMock);

        foreach ($externalMetrics as $metricName => $metricValue) {
            Phake::when($classMetricMock)->__call('get', [$metricName])->thenReturn($metricValue);
        }

        $visitor = new MaintainabilityIndexVisitor($metricsMock);
        $visitor->leaveNode($node);

        Phake::verify($metricsMock)->__call('get', [$nodeName]);
        foreach (array_keys($externalMetrics) as $metricName) {
            Phake::verify($classMetricMock)->__call('get', [$metricName]);
        }
        Phake::verify($classMetricMock)->__call('set', ['mi', $expected['mi']]);
        Phake::verify($classMetricMock)->__call('set', ['mIwoC', $expected['mIwoC']]);
        Phake::verify($classMetricMock)->__call('set', ['commentWeight', $expected['commentWeight']]);
        Phake::verify($metricsMock)->__call('attach', [$classMetricMock]);

        Phake::verifyNoOtherInteractions($classMetricMock);
        Phake::verifyNoOtherInteractions($metricsMock);
    }

    /**
     * Test that nothing occurs when the Node currently being traversed is not of the expected type.
     *
     * @return void
     */
    public function testNoActionIfNodeIsNotCorrectType(): void
    {
        $node = Phake::mock(Node::class);
        $metricsMock = Phake::mock(Metrics::class);
        $visitor = new MaintainabilityIndexVisitor($metricsMock);

        $visitor->leaveNode($node);

        Phake::verifyNoInteraction($node);
        Phake::verifyNoInteraction($metricsMock);
    }

    /**
     * @return Generator<string, array{0: array<string, null|float>, 1: LogicException}>
     */
    public function provideMissingMetrics(): Generator
    {
        $requiredVisitorsByMetrics = [
            'lloc' => LengthVisitor::class,
            'cloc' => LengthVisitor::class,
            'loc' => LengthVisitor::class,
            'ccn' => CyclomaticComplexityVisitor::class,
            'volume' => HalsteadVisitor::class
        ];

        foreach ($requiredVisitorsByMetrics as $setNullMetric => $missingVisitorName) {
            $externalMetricsProvided = ['lloc' => 0, 'cloc' => 1.2, 'loc' => 9.4, 'ccn' => 7, 'volume' => 123.2];
            $externalMetricsProvided[$setNullMetric] = null;
            $exception = new LogicException('Please enable ' . $missingVisitorName . ' visitor first');
            yield 'Without metric "' . $setNullMetric . '"' => [$externalMetricsProvided, $exception];
        }
    }

    /**
     * Test that an exception occurs if this visitor is executed while some required metrics are not previously
     * calculated.
     *
     * @dataProvider provideMissingMetrics
     * @param array<string, null|float> $externalMetrics
     * @param LogicException $expectedException
     * @return void
     */
    //#[DataProvider('provideMissingMetrics')] TODO PHPUnit 10.
    public function testExternalMetricsAreRequired(array $externalMetrics, LogicException $expectedException): void
    {
        $node = Phake::mock(Node\Stmt\Class_::class);
        $node->namespacedName = Phake::mock(Node\Identifier::class);
        Phake::when($node->namespacedName)->__call('toString', [])->thenReturn('UnitTest@Node:Class');
        $metricsMock = Phake::mock(Metrics::class);
        $classMetricMock = Phake::mock(Metric::class);
        $nodeName = MetricNameGenerator::getClassName($node);
        Phake::when($metricsMock)->__call('get', [$nodeName])->thenReturn($classMetricMock);

        foreach ($externalMetrics as $metricName => $metricValue) {
            Phake::when($classMetricMock)->__call('get', [$metricName])->thenReturn($metricValue);
        }

        $visitor = new MaintainabilityIndexVisitor($metricsMock);

        $this->expectExceptionObject($expectedException);
        $visitor->leaveNode($node);
    }
}
