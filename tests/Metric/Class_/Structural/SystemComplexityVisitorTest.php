<?php
declare(strict_types=1);

namespace Tests\Hal\Metric\Class_\Structural;

use Generator;
use Hal\Metric\Class_\Structural\SystemComplexityVisitor;
use Hal\Metric\Helper\MetricNameGenerator;
use Hal\Metric\Helper\SimpleNodeIterator;
use Hal\Metric\Metric;
use Hal\Metric\Metrics;
use Phake;
use PhpParser\Node;
use PHPUnit\Framework\TestCase;

final class SystemComplexityVisitorTest extends TestCase
{
    /**
     * Test that nothing occurs when the Node currently being traversed is not of the expected type.
     *
     * @return void
     */
    public function testNoActionIfNodeIsNotCorrectType(): void
    {
        $node = Phake::mock(Node::class);
        $metricsMock = Phake::mock(Metrics::class);
        // TODO: Replace SimpleNodeIterator with a mock.
        $visitor = new SystemComplexityVisitor($metricsMock, new SimpleNodeIterator());

        $visitor->leaveNode($node);

        Phake::verifyNoInteraction($node);
        Phake::verifyNoInteraction($metricsMock);
    }

    /**
     * @return Generator<string, array{0: Node\Stmt\ClassLike, 1: array{structural: float, data: float, system: float}}>
     */
    public function provideNodesToCalculateSystemComplexity(): Generator
    {
        $node = Phake::mock(Node\Stmt\Class_::class);
        Phake::when($node)->__call('getMethods', [])->thenReturn([]);
        $expected = ['structural' => 0, 'data' => 0, 'system' => 0];
        yield 'Class without method' => [$node, $expected];

        $node = Phake::mock(Node\Stmt\Trait_::class);
        Phake::when($node)->__call('getMethods', [])->thenReturn([]);
        $expected = ['structural' => 0, 'data' => 0, 'system' => 0];
        yield 'Trait without method' => [$node, $expected];

        $node = Phake::mock(Node\Stmt\Class_::class);
        $methods = [
            Phake::mock(Node\Stmt\ClassMethod::class),
            Phake::mock(Node\Stmt\ClassMethod::class),
        ];
        $params = [Phake::mock(Node\Param::class), Phake::mock(Node\Param::class), Phake::mock(Node\Param::class)];
        Phake::when($methods[0])->__call('getParams', [])->thenReturn($params);
        Phake::when($methods[0])->__call('getSubNodeNames', [])->thenReturn(['unitTestSubNodes']);
        $methods[0]->unitTestSubNodes = [];
        Phake::when($methods[1])->__call('getParams', [])->thenReturn([]);
        Phake::when($methods[1])->__call('getSubNodeNames', [])->thenReturn(['unitTestSubNodes']);
        $methods[1]->unitTestSubNodes = [
            Phake::mock(Node\Stmt\Return_::class), // expr is null
            Phake::mock(Node\Stmt\Return_::class), // expr is not null
            Phake::mock(Node\Expr\StaticCall::class),
            Phake::mock(Node\Expr\MethodCall::class),
            Phake::mock(Node\Expr\NullsafeMethodCall::class),
        ];
        $methods[1]->unitTestSubNodes[0]->expr = null;
        $methods[1]->unitTestSubNodes[1]->expr = Phake::mock(Node\Expr::class);
        Phake::when($node)->__call('getMethods', [])->thenReturn($methods);
        $expected = ['structural' => 4.5, 'data' => 1.63, 'system' => 6.13];
        yield 'Class with 2 methods, all use cases covered' => [$node, $expected];
    }

    /**
     * @dataProvider provideNodesToCalculateSystemComplexity
     * @param Node\Stmt\ClassLike $node
     * @param array{structural: float, data: float, system: float} $expected
     * @return void
     */
    //#[DataProvider('provideNodesToCalculateSystemComplexity')] TODO PHPUnit 10.
    public function testICanCalculateSystemComplexity(Node\Stmt\ClassLike $node, array $expected): void
    {
        $node->namespacedName = Phake::mock(Node\Identifier::class);
        Phake::when($node->namespacedName)->__call('toString', [])->thenReturn('UnitTest@Node');
        $metricsMock = Phake::mock(Metrics::class);
        $classMetricMock = Phake::mock(Metric::class);
        $nodeName = MetricNameGenerator::getClassName($node);
        Phake::when($metricsMock)->__call('get', [$nodeName])->thenReturn($classMetricMock);

        // TODO: Replace SimpleNodeIterator with a mock.
        $visitor = new SystemComplexityVisitor($metricsMock, new SimpleNodeIterator());
        $visitor->leaveNode($node);

        Phake::verify($classMetricMock)->__call('set', ['relativeStructuralComplexity', $expected['structural']]);
        Phake::verify($classMetricMock)->__call('set', ['relativeDataComplexity', $expected['data']]);
        Phake::verify($classMetricMock)->__call('set', ['relativeSystemComplexity', $expected['system']]);
        Phake::verify($metricsMock)->__call('get', [$nodeName]);

        Phake::verifyNoOtherInteractions($classMetricMock);
        Phake::verifyNoOtherInteractions($metricsMock);
    }
}
