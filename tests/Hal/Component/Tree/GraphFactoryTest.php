<?php

namespace Test;
use Hal\Component\Tree\GraphFactory;
use Hal\Component\Tree\HashMap;
use Hal\Component\Tree\Node;

/**
 * @group tree
 */
class GraphFactoryTest extends \PHPUnit_Framework_TestCase {

    public function testICanFactoryGraph() {

        // A   -> B, C
        // B   -> A
        // C   -> B, D

        $class1 = $this->getMockBuilder('\Hal\Component\Reflected\Klass')->disableOriginalConstructor()->getMock();
        $class1->method('getFullName')->will($this->returnValue('A'));
        $class1->method('getDependencies')->will($this->returnValue(array('B', 'C')));
        $class2 = $this->getMockBuilder('\Hal\Component\Reflected\Klass')->disableOriginalConstructor()->getMock();
        $class2->method('getFullName')->will($this->returnValue('B'));
        $class2->method('getDependencies')->will($this->returnValue(array('A')));
        $class3 = $this->getMockBuilder('\Hal\Component\Reflected\Klass')->disableOriginalConstructor()->getMock();
        $class3->method('getFullName')->will($this->returnValue('C'));
        $class3->method('getDependencies')->will($this->returnValue(array('B', 'D'))); // unexistant node

        $hash = new HashMap;
        $hash
            ->attach( new Node('A', $class1))
            ->attach( new Node('B', $class2))
            ->attach( new Node('C', $class3));
;
        $factory = new GraphFactory();
        $graph = $factory->factory($hash);

        $this->assertInstanceOf('\Hal\Component\Tree\Graph', $graph);

        //
        // Assertions on edges

        // A
        $expected = array('A -> B', 'A -> C', 'B -> A');
        $a = array();
        foreach ($graph->get('A')->getEdges() as $edge) {
            array_push($a, $edge->asString());
        }
        $this->assertEquals($expected, $a);

        // B
        $expected = array('A -> B', 'B -> A', 'C -> B');
        $b = array();
        foreach ($graph->get('B')->getEdges() as $edge) {
            array_push($b, $edge->asString());
        }
        $this->assertEquals($expected, $b);

        // C
        $expected = array('A -> C', 'C -> B', 'C -> D');
        $c = array();
        foreach ($graph->get('C')->getEdges() as $edge) {
            array_push($c, $edge->asString());
        }
        $this->assertEquals($expected, $c);


        //
        // Assertions on adjacents nodes
        $this->assertEquals(array('B', 'C'), array_keys($graph->get('A')->getAdjacents()));
        $this->assertEquals(array('A', 'C'), array_keys($graph->get('B')->getAdjacents()));
        $this->assertEquals(array('A', 'B', 'D'), array_keys($graph->get('C')->getAdjacents()));
        $this->assertEquals(array('C'), array_keys($graph->get('D')->getAdjacents()));
        $this->assertEquals(4, $graph->count(), 'unexistant adjacent node are added to graph');
    }

}