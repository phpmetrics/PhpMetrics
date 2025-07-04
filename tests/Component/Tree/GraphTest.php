<?php

namespace Test;

use Hal\Component\Tree\Graph;
use Hal\Component\Tree\Node;

/**
 * @group tree
 */
class GraphTest extends \PHPUnit\Framework\TestCase
{
    public function testICanAddEdge(): void
    {
        $graph = new Graph();
        $a = new Node('node_a');
        $b = new Node('node_b');

        $graph->insert($a)->insert($b);
        $graph->addEdge($a, $b);
        $this->assertCount(1, $a->getEdges());
        $this->assertSame($a, $a->getEdges()[0]->getFrom());
        $this->assertSame($b, $a->getEdges()[0]->getTo());

        $this->assertCount(1, $b->getEdges());
        $this->assertSame($a, $a->getEdges()[0]->getFrom());
        $this->assertSame($b, $a->getEdges()[0]->getTo());
    }

    public function testICanAddEdgeWithUnexistantFromNode(): void
    {
        $this->expectException(\LogicException::class);
        $graph = new Graph();
        $a = new Node('A');
        $b = new Node('B');

        $graph->insert($a);
        $graph->addEdge($a, $b);
    }

    public function testICanAddEdgeWithUnexistantToNode(): void
    {
        $this->expectException(\LogicException::class);
        $graph = new Graph();
        $a = new Node('A');
        $b = new Node('B');

        $graph->insert($b);
        $graph->addEdge($a, $b);
    }

    public function testICanInsertSameNodeTwice(): void
    {
        $this->expectException(\LogicException::class);
        $graph = new Graph();
        $node = new Node('A');
        $graph->insert($node);
        $graph->insert($node);
    }

    public function testICanListEdges(): void
    {
        $graph = new Graph();
        $a = new Node('A');
        $b = new Node('B');
        $graph->insert($a);
        $graph->insert($b);

        $this->assertCount(0, $graph->getEdges());
        $graph->addEdge($a, $b);
        $this->assertCount(1, $graph->getEdges());
    }

    public function testEdgeIsAddedToFromAndToNode(): void
    {
        $graph = new Graph();
        $a = new Node('A');
        $b = new Node('B');
        $c = new Node('C');
        $graph->insert($a);
        $graph->insert($b);
        $graph->insert($c);

        $graph->addEdge($a, $b);
        $graph->addEdge($b, $c);

        $this->assertCount(1, $a->getEdges());
        $this->assertCount(2, $b->getEdges());
        $this->assertCount(1, $c->getEdges());
    }


    public function testICanListRootNodes(): void
    {
        $graph = new Graph();
        $a = new Node('A'); // root
        $b = new Node('B');
        $c = new Node('C');
        $d = new Node('D');
        $e = new Node('E'); // root
        $graph->insert($a);
        $graph->insert($b);
        $graph->insert($c);
        $graph->insert($d);
        $graph->insert($e);

        $graph->addEdge($a, $b); // A -> B
        $graph->addEdge($b, $c); // B -> C
        $graph->addEdge($a, $d); // A -> D

        $roots = $graph->getRootNodes();
        $this->assertCount(2, $roots);

        $rootsFound = [
            'A' => false,
            'E' => false,
        ];
        foreach ($roots as $node) {
            $rootsFound[$node->getKey()] = true;
        }

        $this->assertTrue($rootsFound['A']);
        $this->assertTrue($rootsFound['E']);
    }
}
