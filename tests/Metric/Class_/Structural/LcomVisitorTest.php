<?php
declare(strict_types=1);

namespace Tests\Hal\Metric\Class_\Structural;

use Generator;
use Hal\Metric\Class_\Structural\LcomVisitor;
use Hal\Metric\Helper\DetectorInterface;
use Hal\Metric\Helper\MetricNameGenerator;
use Hal\Metric\Helper\SimpleNodeIterator;
use Hal\Metric\Metric;
use Hal\Metric\Metrics;
use Phake;
use PhpParser\Modifiers;
use PhpParser\Node;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

final class LcomVisitorTest extends TestCase
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
        $detector = Phake::mock(DetectorInterface::class);

        // TODO: Replace SimpleNodeIterator with a mock.
        $visitor = new LcomVisitor($metricsMock, new SimpleNodeIterator(), $detector);

        $visitor->leaveNode($node);

        Phake::verifyNoInteraction($node);
        Phake::verifyNoInteraction($metricsMock);
        Phake::verifyNoInteraction($detector);
    }

    /**
     * @return Generator<string, array{Node\Stmt\ClassLike, array{lcom: int}}>
     */
    public static function provideNodesToCalculateLcom(): Generator
    {
        $node = Phake::mock(Node\Stmt\Class_::class);
        Phake::when($node)->__call('getMethods', [])->thenReturn([]);
        $expected = ['lcom' => 0];
        yield 'Class without method' => [$node, $expected];

        $node = Phake::mock(Node\Stmt\Trait_::class);
        Phake::when($node)->__call('getMethods', [])->thenReturn([]);
        $expected = ['lcom' => 0];
        yield 'Trait without method' => [$node, $expected];

        $node = Phake::mock(Node\Stmt\Class_::class);
        $methods = [Phake::mock(Node\Stmt\ClassMethod::class)];
        $methods[0]->name = Phake::mock(Node\Identifier::class);
        Phake::when($methods[0]->name)->__call('__toString', [])->thenReturn('__construct');
        $constructorParams = [Phake::mock(Node\Param::class), Phake::mock(Node\Param::class)];
        $constructorParams[0]->flags = 0; // Not promoted
        $constructorParams[1]->flags = 0; // Not promoted
        Phake::when($methods[0])->__call('getParams', [])->thenReturn($constructorParams);
        Phake::when($methods[0])->__call('getSubNodeNames', [])->thenReturn(['stmts']);
        $methods[0]->stmts = [Phake::mock(Node::class)];
        Phake::when($node)->__call('getMethods', [])->thenReturn($methods);
        $expected = ['lcom' => 1];
        yield 'Class with only constructor, not promoted' => [$node, $expected];

        $node = Phake::mock(Node\Stmt\Class_::class);
        $methods = [
            Phake::mock(Node\Stmt\ClassMethod::class), // constructor (promotes "pub", "pro", "pri", "ro")
            Phake::mock(Node\Stmt\ClassMethod::class), // method A (calls B)
            Phake::mock(Node\Stmt\ClassMethod::class), // method B (fetches "pub", "pro", "pri", "ro")
        ];
        $methods[0]->name = Phake::mock(Node\Identifier::class);
        Phake::when($methods[0]->name)->__call('__toString', [])->thenReturn('__construct');
        $constructorParams = [
            Phake::mock(Node\Param::class), // pub
            Phake::mock(Node\Param::class), // pro
            Phake::mock(Node\Param::class), // pri
            Phake::mock(Node\Param::class), // ro
        ];
        $constructorParams[0]->flags = Modifiers::PUBLIC;
        $constructorParams[0]->var = Phake::mock(Node\Expr\Variable::class);
        $constructorParams[0]->var->name = 'pub';
        $constructorParams[1]->flags = Modifiers::PROTECTED;
        $constructorParams[1]->var = Phake::mock(Node\Expr\Variable::class);
        $constructorParams[1]->var->name = 'pro';
        $constructorParams[2]->flags = Modifiers::PRIVATE;
        $constructorParams[2]->var = Phake::mock(Node\Expr\Variable::class);
        $constructorParams[2]->var->name = 'pri';
        $constructorParams[3]->flags = Modifiers::READONLY;
        $constructorParams[3]->var = Phake::mock(Node\Expr\Variable::class);
        $constructorParams[3]->var->name = 'ro';
        Phake::when($methods[0])->__call('getParams', [])->thenReturn($constructorParams);
        Phake::when($methods[0])->__call('getSubNodeNames', [])->thenReturn(['stmts']);
        $methods[0]->stmts = [];
        $methods[1]->name = Phake::mock(Node\Identifier::class);
        Phake::when($methods[1]->name)->__call('__toString', [])->thenReturn('A');
        Phake::when($methods[1])->__call('getSubNodeNames', [])->thenReturn(['stmts']);
        $methods[1]->stmts = [
            Phake::mock(Node::class), // ignored as not a MethodCall
            Phake::mock(Node\Expr\MethodCall::class), // will call B
            Phake::mock(Node\Expr\MethodCall::class), // ignored as represent something like `(new X())->call();`
            Phake::mock(Node\Expr\MethodCall::class), // ignored as anonymous variable name making the call.
            Phake::mock(Node\Expr\MethodCall::class), // ignored as variable making the call is not `$this`.
        ];
        $methods[1]->stmts[1]->var = Phake::mock(Node\Expr\Variable::class);
        $methods[1]->stmts[1]->var->name = Phake::mock(Node\Identifier::class);
        Phake::when($methods[1]->stmts[1]->var->name)->__call('__toString', [])->thenReturn('this');
        $methods[1]->stmts[1]->name = Phake::mock(Node\Identifier::class);
        Phake::when($methods[1]->stmts[1]->name)->__call('__toString', [])->thenReturn('B');
        $methods[1]->stmts[2]->var = Phake::mock(Node\Expr\New_::class);
        $methods[1]->stmts[3]->var = Phake::mock(Node\Expr::class);
        $methods[1]->stmts[4]->var = Phake::mock(Node\Expr\Variable::class);
        $methods[1]->stmts[4]->var->name = Phake::mock(Node\Identifier::class);
        Phake::when($methods[1]->stmts[4]->var->name)->__call('__toString', [])->thenReturn('otherVar');
        $methods[2]->name = Phake::mock(Node\Identifier::class);
        Phake::when($methods[2]->name)->__call('__toString', [])->thenReturn('B');
        Phake::when($methods[2])->__call('getSubNodeNames', [])->thenReturn(['stmts']);
        $methods[2]->stmts = [
            Phake::mock(Node::class), // ignored as not a PropertyFetch
            Phake::mock(Node\Expr\PropertyFetch::class), // will fetch "pub"
            Phake::mock(Node\Expr\PropertyFetch::class), // will fetch "pro"
            Phake::mock(Node\Expr\PropertyFetch::class), // will fetch "pri"
            Phake::mock(Node\Expr\PropertyFetch::class), // will fetch "ro"
            Phake::mock(Node\Expr\PropertyFetch::class), // ignored as anonymous variable name making the fetch.
            Phake::mock(Node\Expr\PropertyFetch::class), // ignored as variable making the fetch is not `$this`.
        ];
        $methods[2]->stmts[1]->var = Phake::mock(Node\Expr\Variable::class);
        $methods[2]->stmts[1]->var->name = Phake::mock(Node\Identifier::class);
        Phake::when($methods[2]->stmts[1]->var->name)->__call('__toString', [])->thenReturn('this');
        $methods[2]->stmts[1]->name = Phake::mock(Node\Identifier::class);
        Phake::when($methods[2]->stmts[1]->name)->__call('__toString', [])->thenReturn('pub');
        $methods[2]->stmts[2]->var = Phake::mock(Node\Expr\Variable::class);
        $methods[2]->stmts[2]->var->name = Phake::mock(Node\Identifier::class);
        Phake::when($methods[2]->stmts[2]->var->name)->__call('__toString', [])->thenReturn('this');
        $methods[2]->stmts[2]->name = Phake::mock(Node\Identifier::class);
        Phake::when($methods[2]->stmts[2]->name)->__call('__toString', [])->thenReturn('pro');
        $methods[2]->stmts[3]->var = Phake::mock(Node\Expr\Variable::class);
        $methods[2]->stmts[3]->var->name = Phake::mock(Node\Identifier::class);
        Phake::when($methods[2]->stmts[3]->var->name)->__call('__toString', [])->thenReturn('this');
        $methods[2]->stmts[3]->name = Phake::mock(Node\Identifier::class);
        Phake::when($methods[2]->stmts[3]->name)->__call('__toString', [])->thenReturn('pri');
        $methods[2]->stmts[4]->var = Phake::mock(Node\Expr\Variable::class);
        $methods[2]->stmts[4]->var->name = Phake::mock(Node\Identifier::class);
        Phake::when($methods[2]->stmts[4]->var->name)->__call('__toString', [])->thenReturn('this');
        $methods[2]->stmts[4]->name = Phake::mock(Node\Identifier::class);
        Phake::when($methods[2]->stmts[4]->name)->__call('__toString', [])->thenReturn('ro');
        $methods[2]->stmts[5]->var = Phake::mock(Node\Expr::class);
        $methods[2]->stmts[6]->var = Phake::mock(Node\Expr\Variable::class);
        $methods[2]->stmts[6]->var->name = Phake::mock(Node\Identifier::class);
        Phake::when($methods[2]->stmts[6]->var->name)->__call('__toString', [])->thenReturn('otherVar');
        Phake::when($node)->__call('getMethods', [])->thenReturn($methods);
        $expected = ['lcom' => 1];
        yield 'Class lcom 1: promoted-const, 2 methods: A->B, all props. fetched from B' => [$node, $expected];

        $node = Phake::mock(Node\Stmt\Trait_::class);
        $methods = [
            Phake::mock(Node\Stmt\ClassMethod::class), // A
            Phake::mock(Node\Stmt\ClassMethod::class), // B
            Phake::mock(Node\Stmt\ClassMethod::class), // C
            Phake::mock(Node\Stmt\ClassMethod::class), // D (calls E)
            Phake::mock(Node\Stmt\ClassMethod::class), // E
            Phake::mock(Node\Stmt\ClassMethod::class), // F (a getter: ignored)
            Phake::mock(Node\Stmt\ClassMethod::class), // G (a setter: ignored)
        ];
        $methods[0]->name = Phake::mock(Node\Identifier::class);
        Phake::when($methods[0]->name)->__call('__toString', [])->thenReturn('A');
        Phake::when($methods[0])->__call('getSubNodeNames', [])->thenReturn(['stmts']);
        $methods[0]->stmts = [];
        $methods[1]->name = Phake::mock(Node\Identifier::class);
        Phake::when($methods[1]->name)->__call('__toString', [])->thenReturn('B');
        Phake::when($methods[1])->__call('getSubNodeNames', [])->thenReturn(['stmts']);
        $methods[1]->stmts = [];
        $methods[2]->name = Phake::mock(Node\Identifier::class);
        Phake::when($methods[2]->name)->__call('__toString', [])->thenReturn('C');
        Phake::when($methods[2])->__call('getSubNodeNames', [])->thenReturn(['stmts']);
        $methods[2]->stmts = [];
        $methods[3]->name = Phake::mock(Node\Identifier::class);
        Phake::when($methods[3]->name)->__call('__toString', [])->thenReturn('D');
        Phake::when($methods[3])->__call('getSubNodeNames', [])->thenReturn(['stmts']);
        $methods[3]->stmts = [
            Phake::mock(Node\Expr\MethodCall::class), // will call E
        ];
        $methods[3]->stmts[0]->var = Phake::mock(Node\Expr\Variable::class);
        $methods[3]->stmts[0]->var->name = Phake::mock(Node\Identifier::class);
        Phake::when($methods[3]->stmts[0]->var->name)->__call('__toString', [])->thenReturn('this');
        $methods[3]->stmts[0]->name = Phake::mock(Node\Identifier::class);
        Phake::when($methods[3]->stmts[0]->name)->__call('__toString', [])->thenReturn('E');
        $methods[4]->name = Phake::mock(Node\Identifier::class);
        Phake::when($methods[4]->name)->__call('__toString', [])->thenReturn('E');
        Phake::when($methods[4])->__call('getSubNodeNames', [])->thenReturn(['stmts']);
        $methods[4]->stmts = [];
        $methods[5]->name = Phake::mock(Node\Identifier::class);
        Phake::when($methods[5]->name)->__call('__toString', [])->thenReturn('F');
        Phake::when($methods[5])->__call('getSubNodeNames', [])->thenReturn(['stmts']);
        $methods[5]->stmts = [];
        // Fake flag to consider this method as a getter in unit test.
        $methods[5]->flags = 2 ** 29;
        $methods[6]->name = Phake::mock(Node\Identifier::class);
        Phake::when($methods[6]->name)->__call('__toString', [])->thenReturn('G');
        Phake::when($methods[6])->__call('getSubNodeNames', [])->thenReturn(['stmts']);
        $methods[6]->stmts = [];
        // Fake flag to consider this method as a setter in unit test.
        $methods[6]->flags = 2 ** 30;
        Phake::when($node)->__call('getMethods', [])->thenReturn($methods);
        $expected = ['lcom' => 4];
        yield 'Trait with lcom 4: 5 methods: A, B, C, D->E. Methods F and G ignored as accessors' => [$node, $expected];
    }

    /**
     * @param Node\Stmt\ClassLike $node
     * @param array{lcom: int} $expected
     * @return void
     */
    #[DataProvider('provideNodesToCalculateLcom')]
    public function testICanCalculateLcomOnClass(Node\Stmt\ClassLike $node, array $expected): void
    {
        $node->namespacedName = Phake::mock(Node\Name::class);
        Phake::when($node->namespacedName)->__call('toString', [])->thenReturn('UnitTest@Node');
        $metricsMock = Phake::mock(Metrics::class);
        $classMetricMock = Phake::mock(Metric::class);
        $detector = Phake::mock(DetectorInterface::class);
        $nodeName = MetricNameGenerator::getClassName($node);
        Phake::when($metricsMock)->__call('get', [$nodeName])->thenReturn($classMetricMock);
        Phake::when($detector)->__call('detects', [Phake::anyParameters()])->thenReturnCallback(
            static fn (Node $node): string|null => match ($node->flags ?? null) {
                2 ** 29 => 'getter',
                2 ** 30 => 'setter',
                default => null,
            }
        );

        // TODO: Replace SimpleNodeIterator with a mock.
        $visitor = new LcomVisitor($metricsMock, new SimpleNodeIterator(), $detector);
        $visitor->leaveNode($node);

        Phake::verify($classMetricMock)->__call('set', ['lcom', $expected['lcom']]);
        Phake::verify($metricsMock)->__call('get', [$nodeName]);

        Phake::verifyNoOtherInteractions($classMetricMock);
        Phake::verifyNoOtherInteractions($metricsMock);
    }
}
