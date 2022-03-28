<?php
declare(strict_types=1);

namespace Tests\Hal\Metric\Helper;

use Generator;
use Hal\Metric\ClassMetric;
use Hal\Metric\FunctionMetric;
use Hal\Metric\Helper\RegisterMetricsVisitor;
use Hal\Metric\InterfaceMetric;
use Hal\Metric\Metric;
use Hal\Metric\Metrics;
use Phake;
use PhpParser\Node;
use PHPUnit\Framework\TestCase;
use function array_map;

final class RegisterMetricsVisitorTest extends TestCase
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
        $visitor = new RegisterMetricsVisitor($metricsMock);

        $visitor->leaveNode($node);

        Phake::verifyNoInteraction($node);
        Phake::verifyNoInteraction($metricsMock);
    }

    /**
     * @return Generator<string, array{0: Node, 1: array<string, class-string<Metric>>}>
     */
    public function provideNodeToBeRegistered(): Generator
    {
        $node = Phake::mock(Node\Stmt\Class_::class);
        Phake::when($node)->__call('isAnonymous', [])->thenReturn(false);
        $node->namespacedName = Phake::mock(Node\Identifier::class);
        Phake::when($node->namespacedName)->__call('toString', [])->thenReturn('ClassNoMethod');
        Phake::when($node)->__call('getMethods', [])->thenReturn([]);
        yield 'Class without methods' => [$node, ['ClassNoMethod' => ClassMetric::class]];

        $node = Phake::mock(Node\Stmt\Interface_::class);
        $node->namespacedName = Phake::mock(Node\Identifier::class);
        Phake::when($node->namespacedName)->__call('toString', [])->thenReturn('InterfaceNoMethod');
        Phake::when($node)->__call('getMethods', [])->thenReturn([]);
        yield 'Interface without methods' => [$node, ['InterfaceNoMethod' => InterfaceMetric::class]];

        $node = Phake::mock(Node\Stmt\Trait_::class);
        $node->namespacedName = Phake::mock(Node\Identifier::class);
        Phake::when($node->namespacedName)->__call('toString', [])->thenReturn('TraitNoMethod');
        Phake::when($node)->__call('getMethods', [])->thenReturn([]);
        yield 'Trait without methods' => [$node, ['TraitNoMethod' => ClassMetric::class]];

        $node = Phake::mock(Node\Stmt\Enum_::class);
        $node->namespacedName = Phake::mock(Node\Identifier::class);
        Phake::when($node->namespacedName)->__call('toString', [])->thenReturn('EnumNoMethod');
        Phake::when($node)->__call('getMethods', [])->thenReturn([]);
        // Todo: manage Enum.
        //yield 'Enum without methods' => [$node, ['EnumNoMethod' => ClassMetric::class]];

        $node = Phake::mock(Node\Stmt\Function_::class);
        $node->name = Phake::mock(Node\Identifier::class);
        Phake::when($node->name)->__call('toString', [])->thenReturn('Function');
        yield 'Function' => [$node, ['Function' => FunctionMetric::class]];

        $expectedMethods = [];
        $methods = array_map(static function (string $methodName) use (&$expectedMethods): Node\Stmt\ClassMethod {
            $method = Phake::mock(Node\Stmt\ClassMethod::class);
            $method->name = Phake::mock(Node\Identifier::class);
            Phake::when($method->name)->__call('__toString', [])->thenReturn($methodName);
            $expectedMethods[$methodName] = FunctionMetric::class;
            return $method;
        }, ['A', 'B', 'C', 'D', 'E']);

        $node = Phake::mock(Node\Stmt\Class_::class);
        Phake::when($node)->__call('isAnonymous', [])->thenReturn(false);
        $node->namespacedName = Phake::mock(Node\Identifier::class);
        Phake::when($node->namespacedName)->__call('toString', [])->thenReturn('Class+Method');
        Phake::when($node)->__call('getMethods', [])->thenReturn($methods);
        yield 'Class with methods' => [$node, ['Class+Method' => ClassMetric::class, ...$expectedMethods]];

        $node = Phake::mock(Node\Stmt\Interface_::class);
        $node->namespacedName = Phake::mock(Node\Identifier::class);
        Phake::when($node->namespacedName)->__call('toString', [])->thenReturn('Interface+Method');
        Phake::when($node)->__call('getMethods', [])->thenReturn($methods);
        yield 'Interface with methods' => [$node, ['Interface+Method' => InterfaceMetric::class, ...$expectedMethods]];

        $node = Phake::mock(Node\Stmt\Trait_::class);
        $node->namespacedName = Phake::mock(Node\Identifier::class);
        Phake::when($node->namespacedName)->__call('toString', [])->thenReturn('Trait+Method');
        Phake::when($node)->__call('getMethods', [])->thenReturn($methods);
        yield 'Trait with methods' => [$node, ['Trait+Method' => ClassMetric::class, ...$expectedMethods]];

        $node = Phake::mock(Node\Stmt\Enum_::class);
        $node->namespacedName = Phake::mock(Node\Identifier::class);
        Phake::when($node->namespacedName)->__call('toString', [])->thenReturn('Enum+Method');
        Phake::when($node)->__call('getMethods', [])->thenReturn($methods);
        // Todo: manage Enum.
        //yield 'Enum with methods' => [$node, ['Enum+Method' => ClassMetric::class, ...$expectedMethods]];
    }

    /**
     * @dataProvider provideNodeToBeRegistered
     * @param Node $node
     * @param array<string, class-string<Metric>> $expected
     * @return void
     */
    //#[DataProvider('provideNodeToBeRegistered')] TODO: PHPUnit 10.
    public function testICanRegisterMetricsFromNode(Node $node, array $expected): void
    {
        $metrics = new Metrics();
        $visitor = new RegisterMetricsVisitor($metrics);

        $visitor->leaveNode($node);

        foreach ($metrics->all() as $metricName => $metric) {
            self::assertArrayHasKey($metricName, $expected);
            self::assertInstanceOf($expected[$metricName], $metric);
            unset($expected[$metricName]);
        }
        // Once every registered metrics are checked, nothing left from $expected.
        self::assertSame([], $expected);
    }
}
