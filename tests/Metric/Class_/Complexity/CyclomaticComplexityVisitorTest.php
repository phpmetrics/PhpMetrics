<?php
declare(strict_types=1);

namespace Tests\Hal\Metric\Class_\Complexity;

use Generator;
use Hal\Metric\Class_\Complexity\CyclomaticComplexityVisitor;
use Hal\Metric\Helper\MetricNameGenerator;
use Hal\Metric\Metric;
use Hal\Metric\Metrics;
use Phake;
use PhpParser\Node;
use PHPUnit\Framework\TestCase;

final class CyclomaticComplexityVisitorTest extends TestCase
{
    /**
     * @return Generator<string, array{0: Node, 1: array{wmc: int, ccn: int, ccnMethodMax: int}}>
     */
    public function provideNodeToCalculateCyclomaticComplexity(): Generator
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
            $expected = ['wmc' => 0, 'ccn' => 1, 'ccnMethodMax' => 0];
            yield 'With an empty ' . $kind => [$node, $expected];
        }

        $node = Phake::mock(Node\Stmt\Class_::class);
        $node->namespacedName = Phake::mock(Node\Identifier::class);

        $methods = [
            Phake::mock(Node\Stmt\ClassMethod::class), // No nodes in var members.
            Phake::mock(Node\Stmt\ClassMethod::class), // All nodes in single var member.
            Phake::mock(Node\Stmt\ClassMethod::class), // All nodes in several var members.
        ];
        $complexSetOfNodes = [
            Phake::mock(Node\Stmt\If_::class),
            Phake::mock(Node\Stmt\ElseIf_::class),
            Phake::mock(Node\Stmt\For_::class),
            Phake::mock(Node\Stmt\Foreach_::class),
            Phake::mock(Node\Stmt\While_::class),
            Phake::mock(Node\Stmt\Do_::class),
            Phake::mock(Node\Expr\BinaryOp\LogicalAnd::class),
            Phake::mock(Node\Expr\BinaryOp\LogicalOr::class),
            Phake::mock(Node\Expr\BinaryOp\LogicalXor::class),
            Phake::mock(Node\Expr\BinaryOp\BooleanAnd::class),
            Phake::mock(Node\Expr\BinaryOp\BooleanOr::class),
            Phake::mock(Node\Stmt\Catch_::class),
            Phake::mock(Node\Expr\Ternary::class),
            Phake::mock(Node\Expr\BinaryOp\Coalesce::class),
            Phake::mock(Node\Expr\NullsafeMethodCall::class),
            Phake::mock(Node\Expr\NullsafePropertyFetch::class),
            Phake::mock(Node\Expr\BinaryOp\Spaceship::class),
            Phake::mock(Node::class),
            'switch-case' => Phake::mock(Node\Stmt\Case_::class),
            'switch-default' => Phake::mock(Node\Stmt\Case_::class),
            'match-arm-single-cond' => Phake::mock(Node\MatchArm::class),
            'match-arm-many-cond' => Phake::mock(Node\MatchArm::class),
            'match-arm-default' => Phake::mock(Node\MatchArm::class),
        ];
        Phake::when($complexSetOfNodes[0])->__call('getType', [])->thenReturn('Stmt_If');
        Phake::when($complexSetOfNodes[1])->__call('getType', [])->thenReturn('Stmt_ElseIf');
        Phake::when($complexSetOfNodes[2])->__call('getType', [])->thenReturn('Stmt_For');
        Phake::when($complexSetOfNodes[3])->__call('getType', [])->thenReturn('Stmt_Foreach');
        Phake::when($complexSetOfNodes[4])->__call('getType', [])->thenReturn('Stmt_While');
        Phake::when($complexSetOfNodes[5])->__call('getType', [])->thenReturn('Stmt_Do');
        Phake::when($complexSetOfNodes[6])->__call('getType', [])->thenReturn('Expr_BinaryOp_LogicalAnd');
        Phake::when($complexSetOfNodes[7])->__call('getType', [])->thenReturn('Expr_BinaryOp_LogicalOr');
        Phake::when($complexSetOfNodes[8])->__call('getType', [])->thenReturn('Expr_BinaryOp_LogicalXor');
        Phake::when($complexSetOfNodes[9])->__call('getType', [])->thenReturn('Expr_BinaryOp_BooleanAnd');
        Phake::when($complexSetOfNodes[10])->__call('getType', [])->thenReturn('Expr_BinaryOp_BooleanOr');
        Phake::when($complexSetOfNodes[11])->__call('getType', [])->thenReturn('Stmt_Catch');
        Phake::when($complexSetOfNodes[12])->__call('getType', [])->thenReturn('Expr_Ternary');
        Phake::when($complexSetOfNodes[13])->__call('getType', [])->thenReturn('Expr_BinaryOp_Coalesce');
        Phake::when($complexSetOfNodes[14])->__call('getType', [])->thenReturn('Expr_NullsafeMethodCall');
        Phake::when($complexSetOfNodes[15])->__call('getType', [])->thenReturn('Expr_NullsafePropertyFetch');
        Phake::when($complexSetOfNodes[16])->__call('getType', [])->thenReturn('Expr_BinaryOp_Spaceship');
        Phake::when($complexSetOfNodes[17])->__call('getType', [])->thenReturn('');
        Phake::when($complexSetOfNodes['switch-case'])->__call('getType', [])->thenReturn('Stmt_Case');
        Phake::when($complexSetOfNodes['switch-default'])->__call('getType', [])->thenReturn('Stmt_Case');
        Phake::when($complexSetOfNodes['match-arm-single-cond'])->__call('getType', [])->thenReturn('MatchArm');
        Phake::when($complexSetOfNodes['match-arm-many-cond'])->__call('getType', [])->thenReturn('MatchArm');
        Phake::when($complexSetOfNodes['match-arm-default'])->__call('getType', [])->thenReturn('MatchArm');
        $complexSetOfNodes['switch-case']->cond = Phake::mock(Node\Expr::class);
        $complexSetOfNodes['switch-default']->cond = null;
        $complexSetOfNodes['match-arm-single-cond']->conds = [Phake::mock(Node\Expr::class)];
        $complexSetOfNodes['match-arm-many-cond']->conds = [
            Phake::mock(Node\Expr::class),
            Phake::mock(Node\Expr::class),
            Phake::mock(Node\Expr::class),
            Phake::mock(Node\Expr::class)
        ];
        $complexSetOfNodes['match-arm-default']->conds = null;

        $nodeContainingComplexSetOfNodes = Phake::mock(Node::class);
        $nodeContainingComplexSetOfNodes->stmts = $complexSetOfNodes;

        $methods[1]->stmts = $nodeContainingComplexSetOfNodes;
        $methods[2]->stmts = $complexSetOfNodes;

        Phake::when($node)->__call('getMethods', [])->thenReturn($methods);
        Phake::when($node->namespacedName)->__call('toString', [])->thenReturn('UnitTest@Node:ComplexClass');
        $expected = ['wmc' => 51, 'ccn' => 49, 'ccnMethodMax' => 25];
        yield 'With a complex class containing all complex structures' => [$node, $expected];
    }

    /**
     * @dataProvider provideNodeToCalculateCyclomaticComplexity
     * @param Node $node
     * @param array{wmc: int, ccn: int, ccnMethodMax: int} $expected
     * @return void
     */
    //#[DataProvider('provideNodeToCalculateCyclomaticComplexity')] TODO: PHPUnit 10.
    public function testICanCalculateTheCyclomaticComplexityFromNode(Node $node, array $expected): void
    {
        $metricsMock = Phake::mock(Metrics::class);
        $classMetricMock = Phake::mock(Metric::class);
        $nodeName = MetricNameGenerator::getClassName($node);

        Phake::when($metricsMock)->__call('get', [$nodeName])->thenReturn($classMetricMock);

        $visitor = new CyclomaticComplexityVisitor($metricsMock);
        $visitor->leaveNode($node);

        Phake::verify($metricsMock)->__call('get', [$nodeName]);
        Phake::verify($classMetricMock)->__call('set', ['wmc', $expected['wmc']]);
        Phake::verify($classMetricMock)->__call('set', ['ccn', $expected['ccn']]);
        Phake::verify($classMetricMock)->__call('set', ['ccnMethodMax', $expected['ccnMethodMax']]);
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
        $visitor = new CyclomaticComplexityVisitor($metricsMock);

        $visitor->leaveNode($node);

        Phake::verifyNoInteraction($node);
        Phake::verifyNoInteraction($metricsMock);
    }
}
