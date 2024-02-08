<?php
declare(strict_types=1);

namespace Tests\Hal\Metric\Helper;

use Hal\Metric\Helper\SimpleVisitor;
use Phake;
use PhpParser\Node;
use PHPUnit\Framework\TestCase;

final class SimpleVisitorTest extends TestCase
{
    public function testICanVisitTheNode(): void
    {
        $callback = static function (Node $node): void {
            $node->getType();
        };
        $visitor = new SimpleVisitor($callback);

        $node = Phake::mock(Node::class);
        Phake::when($node)->__call('getType', [])->thenReturn('Foo');
        $visitor->leaveNode($node);
        Phake::verify($node)->__call('getType', []);
        Phake::verifyNoOtherInteractions($node);
    }
}
