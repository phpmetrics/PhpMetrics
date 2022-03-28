<?php
declare(strict_types=1);

namespace Tests\Hal\Component\Tree;

use Hal\Component\Tree\Edge;
use Hal\Component\Tree\Node;
use PHPUnit\Framework\TestCase;
use function spl_object_hash;

final class NodeTest extends TestCase
{
    public function testICanRetrieveNodeKey(): void
    {
        $node = new Node('A');
        self::assertSame('A', $node->getKey());
    }

    public function testICanDisplayNode(): void
    {
        $node = new Node('A');
        self::assertSame('A', $node->__toString());
    }

    public function testICanRetrieveNodeData(): void
    {
        $node = new Node('A', 'Test');
        self::assertSame('Test', $node->getData());
    }

    public function testICanOverwriteNodeData(): void
    {
        $node = new Node('A', 'Test');
        $node->setData(['ElePHPant']);
        self::assertNotSame('Test', $node->getData());
        self::assertSame(['ElePHPant'], $node->getData());
    }

    public function testThereIsUniqueIdForEachNode(): void
    {
        $nodeA = new Node('');
        $nodeB = new Node('');

        self::assertNotSame($nodeA->getUniqueId(), $nodeB->getUniqueId());
        self::assertSame(spl_object_hash($nodeA), $nodeA->getUniqueId());
        self::assertSame(spl_object_hash($nodeB), $nodeB->getUniqueId());
    }

    /**
     * Graph looks like:
     * A --> B --> C
     *        \<-- D
     * @return void
     */
    public function testWeCanFindAllNodesRelatedToAnotherOne(): void
    {
        $nodeA = new Node('A');
        $nodeB = new Node('B');
        $nodeC = new Node('C');
        $nodeD = new Node('D');

        $edges = [
            new Edge($nodeA, $nodeB),
            new Edge($nodeB, $nodeC),
            new Edge($nodeD, $nodeB),
        ];

        $nodeA->addEdge($edges[0]);
        $nodeB->addEdge($edges[0]);
        $nodeB->addEdge($edges[1]);
        $nodeC->addEdge($edges[1]);
        $nodeD->addEdge($edges[2]);
        $nodeB->addEdge($edges[2]);

        self::assertSame([$edges[0]], $nodeA->getEdges());
        self::assertSame($edges, $nodeB->getEdges());
        self::assertSame([$edges[1]], $nodeC->getEdges());
        self::assertSame([$edges[2]], $nodeD->getEdges());

        self::assertSame(['B' => $nodeB], $nodeA->getAllNextBy());
        self::assertSame(['A' => $nodeA, 'C' => $nodeC, 'D' => $nodeD], $nodeB->getAllNextBy());
        self::assertSame(['B' => $nodeB], $nodeC->getAllNextBy());
        self::assertSame(['B' => $nodeB], $nodeD->getAllNextBy());
    }
}
