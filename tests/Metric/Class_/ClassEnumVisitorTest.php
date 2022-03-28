<?php
declare(strict_types=1);

namespace Tests\Hal\Metric\Class_;

use Generator;
use Hal\Metric\Class_\ClassEnumVisitor;
use Hal\Metric\FunctionMetric;
use Hal\Metric\Helper\MetricNameGenerator;
use Hal\Metric\Metric;
use Hal\Metric\Metrics;
use Phake;
use PhpParser\Node;
use PhpParser\Node\Stmt\ClassMethod;
use PHPUnit\Framework\TestCase;
use function array_map;
use function array_values;
use function explode;

/**
 * @phpstan-type ClassEnumMetrics array{
 *     _functionMetrics: array<array{role: null|string, public: bool, private: bool}>,
 *     interface: bool,
 *     abstract: bool,
 *     final?: bool,
 *     methods: array<FunctionMetric>,
 *     nbMethods: int,
 *     nbMethodsPrivate: int,
 *     nbMethodsPublic: int
 * }
 */
final class ClassEnumVisitorTest extends TestCase
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
        $visitor = new ClassEnumVisitor($metricsMock);

        $visitor->leaveNode($node);

        Phake::verifyNoInteraction($node);
        Phake::verifyNoInteraction($metricsMock);
    }

    /**
     * @return Generator<string, array{0: Node\Stmt\ClassLike, 1: ClassEnumMetrics, 2: array<string, ClassMethod>}>
     */
    public function provideNodesToCalculateClassEnum(): Generator
    {
        $allowedNodeClasses = [
            'class' => Node\Stmt\Class_::class,
            'interface' => Node\Stmt\Interface_::class,
            'trait' => Node\Stmt\Trait_::class,
        ];
        foreach ($allowedNodeClasses as $kind => $allowedNodeClass) {
            $node = Phake::mock($allowedNodeClass);
            $node->namespacedName = Phake::mock(Node\Identifier::class);
            Phake::when($node->namespacedName)->__call('toString', [])->thenReturn('UnitTest@Node:' . $kind);
            Phake::when($node)->__call('getMethods', [])->thenReturn([]);
            Phake::when($node)->__call('isAbstract', [])->thenReturn(false);
            Phake::when($node)->__call('isFinal', [])->thenReturn(false);
            $expected = [
                '_functionMetrics' => [],
                'interface' => ('interface' === $kind),
                'abstract' => ('interface' === $kind || 'trait' === $kind),
                'final' => false,
                'methods' => [],
                'nbMethods' => 0,
                'nbMethodsPrivate' => 0,
                'nbMethodsPublic' => 0,
            ];
            yield 'No methods for ' . $kind => [$node, $expected, []];
        }

        $node = Phake::mock(Node\Stmt\Class_::class);
        $node->namespacedName = Phake::mock(Node\Identifier::class);
        Phake::when($node->namespacedName)->__call('toString', [])->thenReturn('UnitTest@Node:Class');
        Phake::when($node)->__call('getMethods', [])->thenReturn([]);
        Phake::when($node)->__call('isAbstract', [])->thenReturn(true);
        Phake::when($node)->__call('isFinal', [])->thenReturn(false);
        $expected = [
            '_functionMetrics' => [],
            'interface' => false,
            'abstract' => true,
            'final' => false,
            'methods' => [],
            'nbMethods' => 0,
            'nbMethodsPrivate' => 0,
            'nbMethodsPublic' => 0,
        ];
        yield 'Empty abstract class' => [$node, $expected, []];

        $node = Phake::mock(Node\Stmt\Class_::class);
        $node->namespacedName = Phake::mock(Node\Identifier::class);
        Phake::when($node->namespacedName)->__call('toString', [])->thenReturn('UnitTest@Node:Class');
        Phake::when($node)->__call('getMethods', [])->thenReturn([]);
        Phake::when($node)->__call('isAbstract', [])->thenReturn(false);
        Phake::when($node)->__call('isFinal', [])->thenReturn(true);
        $expected = [
            '_functionMetrics' => [],
            'interface' => false,
            'abstract' => false,
            'final' => true,
            'methods' => [],
            'nbMethods' => 0,
            'nbMethodsPrivate' => 0,
            'nbMethodsPublic' => 0,
        ];
        yield 'Empty final class' => [$node, $expected, []];

        $node = Phake::mock(Node\Stmt\Class_::class);
        $node->namespacedName = Phake::mock(Node\Identifier::class);
        Phake::when($node->namespacedName)->__call('toString', [])->thenReturn('UnitTest@Node:Class');
        $methods = [
            'Public-A' => Phake::mock(ClassMethod::class),
            'Private-B' => Phake::mock(ClassMethod::class),
            'Public-C' => Phake::mock(ClassMethod::class),
            'Private-D' => Phake::mock(ClassMethod::class),
            'Public-E' => Phake::mock(ClassMethod::class),
            'Private-F' => Phake::mock(ClassMethod::class),
            'Public-G' => Phake::mock(ClassMethod::class),
            'Private-H' => Phake::mock(ClassMethod::class),
            'Public-I' => Phake::mock(ClassMethod::class),
        ];
        $functionMetrics = [];
        $functionMetricMocks = [];
        foreach ($methods as $methodKind => $method) {
            [$visibility, $name] = explode('-', $methodKind);
            $method->name = Phake::mock(Node\Identifier::class);
            Phake::when($method->name)->__call('__toString', [])->thenReturn($name);

            $currentFunctionMetrics = [
                'public' => 'Public' === $visibility,
                'private' => 'Public' !== $visibility,
            ];

            Phake::when($method)->__call('isPublic', [])->thenReturn($currentFunctionMetrics['public']);

            $functionMetrics[] = $currentFunctionMetrics;
            $functionMetricMocks[$methodKind] = Phake::mock(FunctionMetric::class);
        }
        Phake::when($node)->__call('getMethods', [])->thenReturn(array_values($methods));
        Phake::when($node)->__call('isAbstract', [])->thenReturn(false);
        Phake::when($node)->__call('isFinal', [])->thenReturn(false);
        $expected = [
            '_functionMetrics' => $functionMetrics,
            'interface' => false,
            'abstract' => false,
            'final' => false,
            'methods' => array_values($functionMetricMocks),
            'nbMethods' => 9,
            'nbMethodsPrivate' => 5,
            'nbMethodsPublic' => 4,
        ];
        yield 'Class with all kind of methods' => [$node, $expected, $methods];
    }

    /**
     * @dataProvider provideNodesToCalculateClassEnum
     * @param Node\Stmt\ClassLike $node
     * @param ClassEnumMetrics $expected
     * @param array<string, Phake\IMock&ClassMethod> $methods
     * @return void
     */
    //#[DataProvider('provideNodesToCalculateClassEnum')] TODO PHPUnit 10.
    public function testICanCalculateClassEnum(Node\Stmt\ClassLike $node, array $expected, array $methods): void
    {
        $metricsMock = Phake::mock(Metrics::class);
        $metricMock = Phake::mock(Metric::class);
        $nodeName = MetricNameGenerator::getClassName($node);
        Phake::when($metricsMock)->__call('get', [$nodeName])->thenReturn($metricMock);
        array_map(static function (ClassMethod $method, FunctionMetric $functionMetricMock) use ($metricsMock): void {
            Phake::when($metricsMock)->__call('get', [(string)$method->name])->thenReturn($functionMetricMock);
        }, $methods, $expected['methods']);

        $visitor = new ClassEnumVisitor($metricsMock);
        $visitor->leaveNode($node);

        Phake::verify($metricMock)->__call('set', ['interface', $expected['interface']]);
        Phake::verify($metricMock)->__call('set', ['abstract', $expected['abstract']]);
        if (!$node instanceof Node\Stmt\Interface_) {
            Phake::verify($metricMock)->__call('set', ['final', $expected['final']]);
        }

        array_map(static function (Phake\IMock $functionMetricMock, array $expectedFunction): void {
            Phake::verify($functionMetricMock)->__call('set', ['public', $expectedFunction['public']]);
            Phake::verify($functionMetricMock)->__call('set', ['private', $expectedFunction['private']]);
            Phake::verifyNoOtherInteractions($functionMetricMock);
        }, $expected['methods'], $expected['_functionMetrics']);

        Phake::verify($metricMock)->__call('set', ['methods', $expected['methods']]);
        Phake::verify($metricMock)->__call('set', ['nbMethods', $expected['nbMethods']]);
        Phake::verify($metricMock)->__call('set', ['nbMethodsPrivate', $expected['nbMethodsPrivate']]);
        Phake::verify($metricMock)->__call('set', ['nbMethodsPublic', $expected['nbMethodsPublic']]);
        Phake::verify($metricsMock)->__call('get', [$nodeName]);
        array_map(static function (ClassMethod $method) use ($metricsMock): void {
            Phake::verify($metricsMock)->__call('get', [(string)$method->name]);
        }, $methods);

        Phake::verifyNoOtherInteractions($metricMock);
        Phake::verifyNoOtherInteractions($metricsMock);
    }
}
