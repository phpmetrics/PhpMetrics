<?php

namespace Test;

use Hal\Component\Tree\Node;

/**
 * @group tree
 */
class NodeTest extends \PHPUnit_Framework_TestCase {

    public function testICanWorkWithNode() {

        $node = new Node('A');
        $node->addAdjacent($nodeB = new Node('B'));
        $node->setData('value1');

        $this->assertEquals('value1', $node->getData());
        $this->assertEquals(array($nodeB), $node->getAdjacents());
        $this->assertEquals('A', $node->getKey());

    }

}