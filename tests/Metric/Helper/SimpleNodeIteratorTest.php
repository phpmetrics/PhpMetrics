<?php
declare(strict_types=1);

namespace Tests\Hal\Metric\Helper;

use Hal\Metric\Helper\SimpleNodeIterator;
use Phake;
use PhpParser\Node;
use PHPUnit\Framework\TestCase;

final class SimpleNodeIteratorTest extends TestCase
{
    public function testICanIterateSimpleCallbackOnNode(): void
    {
        $node = Phake::mock(Node::class);
        $callback = static function (Node $node): void {
            /** @noinspection PhpUndefinedFieldInspection Willing to be undefined, to be checked later. */
            $node->unitTestData = 'FooBar';
        };
        Phake::when($node)->__call('getSubNodeNames', [])->thenReturn(['unitTestSubNodes']);

        $nodeIterator = new SimpleNodeIterator();
        $nodeIterator->iterateOver($node, $callback);

        Phake::verify($node)->__call('getSubNodeNames', []);
        Phake::verifyNoOtherInteractions($node);
        self::assertSame('FooBar', $node->unitTestData);
    }
}
