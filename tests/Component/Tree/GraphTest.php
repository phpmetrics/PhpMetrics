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
class GraphTest extends \PHPUnit_Framework_TestCase {

    public function testICanAddEdge() {
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
    public function testICanAddEdgeWithUnexistantFromNode() {
        $graph = new Graph();
        $a = new Node('A');
        $b = new Node('B');

        $graph->insert($a);
        $graph->addEdge($a, $b);
    }

    /**
     * @expectedException \LogicException
     */
    public function testICanAddEdgeWithUnexistantToNode() {
        $graph = new Graph();
        $a = new Node('A');
        $b = new Node('B');

        $graph->insert($b);
        $graph->addEdge($a, $b);
    }

    /**
     * @expectedException \LogicException
     */
    public function testICanInsertSameNodeTwice() {
        $graph = new Graph();
        $node = new Node('A');
        $graph->insert($node);
        $graph->insert($node);
    }

    public function testICanListEdges() {
        $graph = new Graph();
        $a = new Node('A');
        $b = new Node('B');
        $graph->insert($a);
        $graph->insert($b);

        $this->assertEquals(0, sizeof($graph->getEdges()));
        $graph->addEdge($a, $b);
        $this->assertEquals(1, sizeof($graph->getEdges()));
    }

    public function testEdgeIsAddedToFromAndToNode() {
        $graph = new Graph();
        $a = new Node('A');
        $b = new Node('B');
        $c = new Node('C');
        $graph->insert($a);
        $graph->insert($b);
        $graph->insert($c);

        $graph->addEdge($a, $b);
        $graph->addEdge($b, $c);

        $this->assertEquals(1, sizeof($a->getEdges()));
        $this->assertEquals(2, sizeof($b->getEdges()));
        $this->assertEquals(1, sizeof($c->getEdges()));
    }

}