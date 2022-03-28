<?php
declare(strict_types=1);

namespace Tests\Hal\Metric\Class_\Text;

use Generator;
use Hal\Metric\Class_\Text\HalsteadVisitor;
use Hal\Metric\Helper\MetricNameGenerator;
use Hal\Metric\Helper\SimpleNodeIterator;
use Hal\Metric\Metric;
use Hal\Metric\Metrics;
use Phake;
use PhpParser\Node;
use PHPUnit\Framework\TestCase;

/**
 * @phpstan-type HalsteadMetrics array{
 *     length: int,
 *     vocabulary: int,
 *     volume: float,
 *     difficulty: float,
 *     effort: float,
 *     level: float,
 *     bugs: float,
 *     time: float,
 *     intelligentContent: float,
 *     number_operators: int,
 *     number_operands: int,
 *     number_operators_unique: int,
 *     number_operands_unique: int
 * }
 */
final class HalsteadVisitorTest extends TestCase
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
        $visitor = new HalsteadVisitor($metricsMock, new SimpleNodeIterator());

        $visitor->leaveNode($node);

        Phake::verifyNoInteraction($node);
        Phake::verifyNoInteraction($metricsMock);
    }

    /**
     * @return Generator<string, array{0: Node\Stmt\ClassLike|Node\Stmt\Function_, 1: HalsteadMetrics}>
     */
    public function provideNodesToCalculateHalstead(): Generator
    {
        $node = Phake::mock(Node\Stmt\Class_::class);
        Phake::when($node)->__call('getSubNodeNames', [])->thenReturn(['unitTestSubNodes']);
        $node->unitTestSubNodes = [];
        $expected = [
            'length' => 0,
            'vocabulary' => 0,
            'volume' => 0,
            'difficulty' => 0,
            'effort' => 0,
            'level' => 0,
            'bugs' => 0,
            'time' => 0,
            'intelligentContent' => 0,
            'number_operators' => 0,
            'number_operands' => 0,
            'number_operators_unique' => 0,
            'number_operands_unique' => 0,
        ];
        yield 'Class without any sub nodes' => [$node, $expected];

        $node = Phake::mock(Node\Stmt\Trait_::class);
        Phake::when($node)->__call('getSubNodeNames', [])->thenReturn(['unitTestSubNodes']);
        $node->unitTestSubNodes = [];
        $expected = [
            'length' => 0,
            'vocabulary' => 0,
            'volume' => 0,
            'difficulty' => 0,
            'effort' => 0,
            'level' => 0,
            'bugs' => 0,
            'time' => 0,
            'intelligentContent' => 0,
            'number_operators' => 0,
            'number_operands' => 0,
            'number_operators_unique' => 0,
            'number_operands_unique' => 0,
        ];
        yield 'Trait without any sub nodes' => [$node, $expected];

        $node = Phake::mock(Node\Stmt\Function_::class);
        Phake::when($node)->__call('getSubNodeNames', [])->thenReturn(['unitTestSubNodes']);
        $node->unitTestSubNodes = [];
        $expected = [
            'length' => 0,
            'vocabulary' => 0,
            'volume' => 0,
            'difficulty' => 0,
            'effort' => 0,
            'level' => 0,
            'bugs' => 0,
            'time' => 0,
            'intelligentContent' => 0,
            'number_operators' => 0,
            'number_operands' => 0,
            'number_operators_unique' => 0,
            'number_operands_unique' => 0,
        ];
        yield 'Function without any sub nodes' => [$node, $expected];

        $node = Phake::mock(Node\Stmt\Class_::class);
        Phake::when($node)->__call('getSubNodeNames', [])->thenReturn(['unitTestSubNodes']);
        $node->unitTestSubNodes = [
            Phake::mock(Node\Param::class), // Ignored as Param->var is Variable.
            Phake::mock(Node\Expr\BinaryOp::class),
            Phake::mock(Node\Expr\AssignOp::class),
            Phake::mock(Node\Stmt\If_::class),
            Phake::mock(Node\Stmt\If_::class), // Second with same name (to differ from number of unique operator)
            Phake::mock(Node\Stmt\If_::class), // Third with same name (to differ from number of unique operator)
            Phake::mock(Node\Stmt\For_::class),
            Phake::mock(Node\Stmt\Switch_::class),
            Phake::mock(Node\Stmt\Catch_::class),
            Phake::mock(Node\Stmt\Return_::class),
            Phake::mock(Node\Stmt\While_::class),
            Phake::mock(Node\Expr\Assign::class),
            Phake::mock(Node\Expr\Cast::class), // using value as node name.
            Phake::mock(Node\Expr\Cast::class), // using name as node name.
            Phake::mock(Node\Expr\Cast::class), // using getType as node name, same name as value.
            Phake::mock(Node\Expr\Variable::class), // using value as node name.
            Phake::mock(Node\Expr\Variable::class), // using name as node name.
            Phake::mock(Node\Param::class), // using value as node name.
            Phake::mock(Node\Param::class), // using name as node name.
            Phake::mock(Node\Param::class), // using getType as node name, same name as value.
            Phake::mock(Node\Scalar::class), // using value as node name.
            Phake::mock(Node\Scalar::class), // using name as node name.
            Phake::mock(Node\Scalar::class), // using getType as node name, same name as value.
        ];
        $node->unitTestSubNodes[0]->var = Phake::mock(Node\Expr\Variable::class);
        Phake::when($node->unitTestSubNodes[1])->__call('getType', [])->thenReturn('BinaryOp');
        Phake::when($node->unitTestSubNodes[2])->__call('getType', [])->thenReturn('AssignOp');
        Phake::when($node->unitTestSubNodes[3])->__call('getType', [])->thenReturn('If_');
        Phake::when($node->unitTestSubNodes[4])->__call('getType', [])->thenReturn('If_');
        Phake::when($node->unitTestSubNodes[5])->__call('getType', [])->thenReturn('If_');
        Phake::when($node->unitTestSubNodes[6])->__call('getType', [])->thenReturn('For_');
        Phake::when($node->unitTestSubNodes[7])->__call('getType', [])->thenReturn('Switch_');
        Phake::when($node->unitTestSubNodes[8])->__call('getType', [])->thenReturn('Catch_');
        Phake::when($node->unitTestSubNodes[9])->__call('getType', [])->thenReturn('Return_');
        Phake::when($node->unitTestSubNodes[10])->__call('getType', [])->thenReturn('While_');
        Phake::when($node->unitTestSubNodes[11])->__call('getType', [])->thenReturn('Assign');
        $node->unitTestSubNodes[12]->value = 'Cast';
        $node->unitTestSubNodes[13]->name = Phake::mock(Node\Expr::class);
        Phake::when($node->unitTestSubNodes[14])->__call('getType', [])->thenReturn('Cast');
        $node->unitTestSubNodes[15]->value = 'Variable';
        $node->unitTestSubNodes[16]->name = Phake::mock(Node\Expr::class);
        $node->unitTestSubNodes[17]->value = 'Param';
        $node->unitTestSubNodes[18]->name = Phake::mock(Node\Expr::class);
        Phake::when($node->unitTestSubNodes[19])->__call('getType', [])->thenReturn('Param');
        $node->unitTestSubNodes[20]->value = 'Scalar';
        $node->unitTestSubNodes[21]->name = Phake::mock(Node\Expr::class);
        Phake::when($node->unitTestSubNodes[22])->__call('getType', [])->thenReturn('Scalar');
        $expected = [
            'length' => 22,
            'vocabulary' => 17,
            'volume' => 89.92,
            'difficulty' => 6.19,
            'effort' => 556.41,
            'level' => 0.16,
            'bugs' => 0.02,
            'time' => 31,
            'intelligentContent' => 14.53,
            'number_operators' => 11,
            'number_operands' => 11,
            'number_operators_unique' => 9,
            'number_operands_unique' => 8,
        ];
        yield 'Class with several operators and operands' => [$node, $expected];

        $node = Phake::mock(Node\Stmt\Class_::class);
        Phake::when($node)->__call('getSubNodeNames', [])->thenReturn(['unitTestSubNodes']);
        $node->unitTestSubNodes = [
            Phake::mock(Node\Param::class), // Ignored as Param->var is Variable.
            Phake::mock(Node\Expr\BinaryOp::class),
            Phake::mock(Node\Expr\AssignOp::class),
            Phake::mock(Node\Stmt\If_::class),
            Phake::mock(Node\Stmt\If_::class), // Second with same name (to differ from number of unique operator)
            Phake::mock(Node\Stmt\If_::class), // Third with same name (to differ from number of unique operator)
            Phake::mock(Node\Stmt\For_::class),
            Phake::mock(Node\Stmt\Switch_::class),
            Phake::mock(Node\Stmt\Catch_::class),
            Phake::mock(Node\Stmt\Return_::class),
            Phake::mock(Node\Stmt\While_::class),
            Phake::mock(Node\Expr\Assign::class),
        ];
        $node->unitTestSubNodes[0]->var = Phake::mock(Node\Expr\Variable::class);
        Phake::when($node->unitTestSubNodes[1])->__call('getType', [])->thenReturn('BinaryOp');
        Phake::when($node->unitTestSubNodes[2])->__call('getType', [])->thenReturn('AssignOp');
        Phake::when($node->unitTestSubNodes[3])->__call('getType', [])->thenReturn('If_');
        Phake::when($node->unitTestSubNodes[4])->__call('getType', [])->thenReturn('If_');
        Phake::when($node->unitTestSubNodes[5])->__call('getType', [])->thenReturn('If_');
        Phake::when($node->unitTestSubNodes[6])->__call('getType', [])->thenReturn('For_');
        Phake::when($node->unitTestSubNodes[7])->__call('getType', [])->thenReturn('Switch_');
        Phake::when($node->unitTestSubNodes[8])->__call('getType', [])->thenReturn('Catch_');
        Phake::when($node->unitTestSubNodes[9])->__call('getType', [])->thenReturn('Return_');
        Phake::when($node->unitTestSubNodes[10])->__call('getType', [])->thenReturn('While_');
        Phake::when($node->unitTestSubNodes[11])->__call('getType', [])->thenReturn('Assign');
        $expected = [
            'length' => 0,
            'vocabulary' => 0,
            'volume' => 0,
            'difficulty' => 0,
            'effort' => 0,
            'level' => 0,
            'bugs' => 0,
            'time' => 0,
            'intelligentContent' => 0,
            'number_operators' => 0,
            'number_operands' => 0,
            'number_operators_unique' => 0,
            'number_operands_unique' => 0,
        ];
        yield 'Class without operands' => [$node, $expected];
    }

    /**
     * @dataProvider provideNodesToCalculateHalstead
     * @param Node\Stmt\ClassLike|Node\Stmt\Function_ $node
     * @param HalsteadMetrics $expected
     * @return void
     */
    //#[DataProvider('provideNodesToCalculateHalstead')] TODO PHPUnit 10.
    public function testICanCalculateHalstead(Node\Stmt\ClassLike|Node\Stmt\Function_ $node, array $expected): void
    {
        $metricsMock = Phake::mock(Metrics::class);
        $metricMock = Phake::mock(Metric::class);
        if ($node instanceof Node\Stmt\Function_) {
            $node->name = Phake::mock(Node\Identifier::class);
            Phake::when($node->name)->__call('toString', [])->thenReturn('UnitTest@Node');
            $nodeName = MetricNameGenerator::getFunctionName($node);
        } else {
            $node->namespacedName = Phake::mock(Node\Identifier::class);
            Phake::when($node->namespacedName)->__call('toString', [])->thenReturn('UnitTest@Node');
            $nodeName = MetricNameGenerator::getClassName($node);
        }
        Phake::when($metricsMock)->__call('get', [$nodeName])->thenReturn($metricMock);

        // TODO: Replace SimpleNodeIterator with a mock.
        $visitor = new HalsteadVisitor($metricsMock, new SimpleNodeIterator());
        $visitor->leaveNode($node);

        Phake::verify($metricMock)->__call('set', ['length', $expected['length']]);
        Phake::verify($metricMock)->__call('set', ['vocabulary', $expected['vocabulary']]);
        Phake::verify($metricMock)->__call('set', ['volume', $expected['volume']]);
        Phake::verify($metricMock)->__call('set', ['difficulty', $expected['difficulty']]);
        Phake::verify($metricMock)->__call('set', ['effort', $expected['effort']]);
        Phake::verify($metricMock)->__call('set', ['level', $expected['level']]);
        Phake::verify($metricMock)->__call('set', ['bugs', $expected['bugs']]);
        Phake::verify($metricMock)->__call('set', ['time', $expected['time']]);
        Phake::verify($metricMock)->__call('set', ['intelligentContent', $expected['intelligentContent']]);
        Phake::verify($metricMock)->__call('set', ['number_operators', $expected['number_operators']]);
        Phake::verify($metricMock)->__call('set', ['number_operands', $expected['number_operands']]);
        Phake::verify($metricMock)->__call('set', ['number_operators_unique', $expected['number_operators_unique']]);
        Phake::verify($metricMock)->__call('set', ['number_operands_unique', $expected['number_operands_unique']]);
        Phake::verify($metricsMock)->__call('get', [$nodeName]);

        Phake::verifyNoOtherInteractions($metricMock);
        Phake::verifyNoOtherInteractions($metricsMock);
    }
}
