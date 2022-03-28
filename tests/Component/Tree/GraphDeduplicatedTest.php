<?php
declare(strict_types=1);

namespace Tests\Hal\Component\Tree;

use Hal\Component\Tree\GraphDeduplicated;
use Hal\Component\Tree\Node;
use PHPUnit\Framework\TestCase;

final class GraphDeduplicatedTest extends TestCase
{
    public function testICantHaveTwiceTheSameEdge(): void
    {
        $graph = new GraphDeduplicated();
        $nodeA = new Node('A');
        $nodeB = new Node('B');
        $graph->insert($nodeA);
        $graph->insert($nodeB);

        $graph->addEdge($nodeA, $nodeB);
        $graph->addEdge($nodeA, $nodeB);

        $expectedOutput = "A;\nB;\nA ➔ B;\n";
        self::assertSame($expectedOutput, $graph->__toString());
    }
}
