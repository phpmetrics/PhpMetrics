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
            /** @noinspection PhpUndefinedFieldInspection Willing to be undefined, to be checked later. */
            $node->unitTestData = 'FooBar';
        };
        $visitor = new SimpleVisitor($callback);

        $node = Phake::mock(Node::class);
        $visitor->leaveNode($node);
        self::assertSame('FooBar', $node->unitTestData);
    }
}
