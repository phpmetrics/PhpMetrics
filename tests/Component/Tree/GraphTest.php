<?php

namespace Test;

use Hal\Component\Tree\Edge;
use Hal\Component\Tree\Graph;
use Hal\Component\Tree\GraphFactory;
use Hal\Component\Tree\HashMap;
use Hal\Component\Tree\Node;

/**
 * @group tree
 */
class GraphTest extends \PHPUnit_Framework_TestCase
{

    public function testICanAddEdge()
    {
        $graph = new Graph();
        $a = $this->getMockBuilder('\\Hal\\Component\\Tree\\Node')->disableOriginalConstructor()->getMock();
        $a->method('getKey')->will($this->returnValue('A'));
        $a->expects($this->once())->method('addEdge');
        $b = new Node('B');

        $graph->insert($a)->insert($b);
        $graph->addEdge($a, $b);
    }

    /**
     * @expectedException \LogicException
     */
    public function testICanAddEdgeWithUnexistantFromNode()
    {
        $graph = new Graph();
        $a = new Node('A');
        $b = new Node('B');

        $graph->insert($a);
        $graph->addEdge($a, $b);
    }

    /**
     * @expectedException \LogicException
     */
    public function testICanAddEdgeWithUnexistantToNode()
    {
        $graph = new Graph();
        $a = new Node('A');
        $b = new Node('B');

        $graph->insert($b);
        $graph->addEdge($a, $b);
    }

    /**
     * @expectedException \LogicException
     */
    public function testICanInsertSameNodeTwice()
    {
        $graph = new Graph();
        $node = new Node('A');
        $graph->insert($node);
        $graph->insert($node);
    }

    public function testICanListEdges()
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

    public function testEdgeIsAddedToFromAndToNode()
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


    public function testICanListRootNodes()
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
