<?php
declare(strict_types=1);

namespace Tests\Hal\Metric\Class_\Complexity;

use Generator;
use Hal\Metric\Class_\Complexity\KanDefectVisitor;
use Hal\Metric\Helper\MetricNameGenerator;
use Hal\Metric\Helper\SimpleNodeIterator;
use Hal\Metric\Metric;
use Hal\Metric\Metrics;
use Phake;
use PhpParser\Node;
use PHPUnit\Framework\TestCase;

final class KanDefectVisitorTest extends TestCase
{
    /**
     * @return Generator<string, array{0: Node, 1: array{kanDefect: float}}>
     */
    public function provideNodeToCalculateKanDefect(): Generator
    {
        $allowedNodeClasses = [
            'class' => Node\Stmt\Class_::class,
            'interface' => Node\Stmt\Interface_::class,
            'trait' => Node\Stmt\Trait_::class
        ];
        foreach ($allowedNodeClasses as $kind => $allowedNodeClass) {
            $node = Phake::mock($allowedNodeClass);
            $node->namespacedName = Phake::mock(Node\Identifier::class);
            Phake::when($node)->__call('getMethods', [])->thenReturn([]);
            Phake::when($node->namespacedName)->__call('toString', [])->thenReturn('UnitTest@Node:' . $kind);
            $expected = ['kanDefect' => 0.15];
            yield 'With an empty ' . $kind => [$node, $expected];
        }

        $node = Phake::mock(Node\Stmt\Class_::class);
        $node->namespacedName = Phake::mock(Node\Identifier::class);
        Phake::when($node->namespacedName)->__call('toString', [])->thenReturn('UnitTest@Node:SimpleClass');
        Phake::when($node)->__call('getSubNodeNames', [])->thenReturn(['stmtsForUnitTest']);
        $node->stmtsForUnitTest = [
            Phake::mock(Node\Stmt\Do_::class), //doWhile: 1
            Phake::mock(Node\Stmt\Foreach_::class), //doWhile: 2
            Phake::mock(Node\Stmt\While_::class), //doWhile: 3
            Phake::mock(Node\Stmt\If_::class), //if: 1
            Phake::mock(Node\Stmt\Switch_::class), //select: 1
            Phake::mock(Node\Expr\Match_::class), //select: 2
        ];
        // Expected Kan's defect is 0.15 + 0.23 * 3 + 0.22 * 2 + 0.07 * 1 = 1.35
        $expected = ['kanDefect' => 1.35];
        yield "With a class containing only once each structure that increases Kan's defect" => [$node, $expected];

        $node = Phake::mock(Node\Stmt\Class_::class);
        $node->namespacedName = Phake::mock(Node\Identifier::class);
        Phake::when($node->namespacedName)->__call('toString', [])->thenReturn('UnitTest@Node:ComplexClass');
        Phake::when($node)->__call('getSubNodeNames', [])->thenReturn(['stmtsForUnitTest']);
        $node->stmtsForUnitTest = [
            Phake::mock(Node\Stmt\Do_::class), //doWhile: 1
            Phake::mock(Node\Stmt\Foreach_::class), //doWhile: 2
            Phake::mock(Node\Stmt\While_::class), //doWhile: 3
            Phake::mock(Node\Stmt\If_::class), //if: 1
            Phake::mock(Node\Stmt\If_::class), //if: 2
            Phake::mock(Node\Stmt\If_::class), //if: 3
            Phake::mock(Node\Stmt\Global_::class), // noop
            Phake::mock(Node\Stmt\If_::class), //if: 4
            Phake::mock(Node\Stmt\ElseIf_::class), // noop
            Phake::mock(Node\Stmt\Foreach_::class), //doWhile: 4
            Phake::mock(Node\Stmt\Switch_::class), //select: 1
            Phake::mock(Node\Stmt\Switch_::class), //select: 2
            Phake::mock(Node\Stmt\UseUse::class), // noop
            Phake::mock(Node\Expr\Match_::class), //select: 3
            Phake::mock(Node\Expr\Match_::class), //select: 4
        ];
        // Expected Kan's defect is 0.15 + 0.23 * 4 + 0.22 * 4 + 0.07 * 4 = 2.23
        $expected = ['kanDefect' => 2.23];
        yield "With a class containing nodes that increases Kan's defect or not" => [$node, $expected];
    }

    /**
     * @dataProvider provideNodeToCalculateKanDefect
     * @param Node $node
     * @param array{kanDefect: float} $expected
     * @return void
     */
    //#[DataProvider('provideNodeToCalculateKanDefect')] TODO: PHPUnit 10.
    public function testICanCalculateTheKanDefectFromNode(Node $node, array $expected): void
    {
        $metricsMock = Phake::mock(Metrics::class);
        $classMetricMock = Phake::mock(Metric::class);
        $nodeName = MetricNameGenerator::getClassName($node);

        Phake::when($metricsMock)->__call('get', [$nodeName])->thenReturn($classMetricMock);

        // TODO: Replace SimpleNodeIterator with a mock.
        $visitor = new KanDefectVisitor($metricsMock, new SimpleNodeIterator());
        $visitor->leaveNode($node);

        Phake::verify($metricsMock)->__call('get', [$nodeName]);
        Phake::verify($classMetricMock)->__call('set', ['kanDefect', $expected['kanDefect']]);
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
        // TODO: Replace SimpleNodeIterator with a mock.
        $visitor = new KanDefectVisitor($metricsMock, new SimpleNodeIterator());

        $visitor->leaveNode($node);

        Phake::verifyNoInteraction($node);
        Phake::verifyNoInteraction($metricsMock);
    }
}
