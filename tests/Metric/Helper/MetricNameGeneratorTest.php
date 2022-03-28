<?php
declare(strict_types=1);

namespace Tests\Hal\Metric\Helper;

use Generator;
use Hal\Metric\Helper\MetricNameGenerator;
use Phake;
use PhpParser\Node;
use PHPUnit\Framework\TestCase;
use function spl_object_hash;

final class MetricNameGeneratorTest extends TestCase
{
    /**
     * @return Generator<string, array{0: Node, 1: string}>
     */
    public function provideClassNodeToGetClassName(): Generator
    {
        $node = Phake::mock(Node\Stmt\Class_::class);
        Phake::when($node)->__call('isAnonymous', [])->thenReturn(true);
        $expected = 'anonymous@' . spl_object_hash($node);
        yield 'With an anonymous class' => [$node, $expected];

        $node = Phake::mock(Node\Stmt\Class_::class);
        Phake::when($node)->__call('isAnonymous', [])->thenReturn(false);
        $node->namespacedName = Phake::mock(Node\Identifier::class);
        Phake::when($node->namespacedName)->__call('toString', [])->thenReturn('FooBar');
        $expected = 'FooBar';
        yield 'With a named class, by namespace' => [$node, $expected];

        $node = Phake::mock(Node::class);
        $node->name = Phake::mock(Node\Identifier::class);
        Phake::when($node->name)->__call('toString', [])->thenReturn('FooBar');
        $expected = 'FooBar';
        yield 'With a named node' => [$node, $expected];

        $node = Phake::mock(Node::class);
        $expected = 'unknown@' . spl_object_hash($node);
        yield 'With an unknown node' => [$node, $expected];
    }

    /**
     * @dataProvider provideClassNodeToGetClassName
     * @param Node $node
     * @param string $expected
     * @return void
     */
    //#[DataProvider('provideClassNodeToGetClassName')] TODO: PHPUnit 10.
    public function testICanInferClassNameFromClassNode(Node $node, string $expected): void
    {
        self::assertSame($expected, MetricNameGenerator::getClassName($node));
    }

    /**
     * @return Generator<string, array{0: Node, 1: string}>
     */
    public function provideNodeToGetFunctionName(): Generator
    {
        $node = Phake::mock(Node::class);
        $node->name = Phake::mock(Node\Identifier::class);
        Phake::when($node->name)->__call('toString', [])->thenReturn('FooBar');
        $expected = 'FooBar';
        yield 'With a named node' => [$node, $expected];

        $node = Phake::mock(Node::class);
        $expected = 'unknown@' . spl_object_hash($node);
        yield 'With an unknown node' => [$node, $expected];
    }

    /**
     * @dataProvider provideNodeToGetFunctionName
     * @param Node $node
     * @param string $expected
     * @return void
     */
    //#[DataProvider('provideNodeToGetFunctionName')] TODO: PHPUnit 10.
    public function testICanInferFunctionNameFromFunctionNode(Node $node, string $expected): void
    {
        self::assertSame($expected, MetricNameGenerator::getFunctionName($node));
    }
}
