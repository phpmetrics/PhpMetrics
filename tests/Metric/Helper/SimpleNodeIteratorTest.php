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
        Phake::when($node)->__call('getType', [])->thenReturn('Foo');
        $callback = static function (Node $node): void {
            $node->getType();
        };
        Phake::when($node)->__call('getSubNodeNames', [])->thenReturn([]);

        $nodeIterator = new SimpleNodeIterator();
        $nodeIterator->iterateOver($node, $callback);

        Phake::verify($node)->__call('getSubNodeNames', []);
        Phake::verify($node)->__call('getType', []);
        Phake::verifyNoOtherInteractions($node);
    }
}
