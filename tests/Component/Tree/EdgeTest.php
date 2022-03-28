<?php
declare(strict_types=1);

namespace Tests\Hal\Component\Tree;

use Hal\Component\Tree\Edge;
use Hal\Component\Tree\Node;
use PHPUnit\Framework\TestCase;

final class EdgeTest extends TestCase
{
    public function testICanDisplayAnEdge(): void
    {
        $nodeA = new Node('A');
        $nodeB = new Node('B');

        $edge = new Edge($nodeA, $nodeB);
        self::assertSame('A ➔ B', $edge->__toString());
    }
}
