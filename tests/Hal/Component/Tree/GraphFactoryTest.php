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

        $factory = new GraphFactory();
        $graph = $factory->factory($hash);

        $this->assertInstanceOf('\Hal\Component\Tree\Graph', $graph);

        $this->assertEquals(2, sizeof($graph->get('A')->getAdjacents()));
        $this->assertEquals(1, sizeof($graph->get('B')->getAdjacents()));
        $this->assertEquals(2, sizeof($graph->get('C')->getAdjacents()));
        $this->assertEquals(4, $graph->count(), 'unexistant adjacent node are added to graph');
    }

}