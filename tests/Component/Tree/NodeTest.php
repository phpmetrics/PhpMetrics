<?php

namespace Test;

use Hal\Component\Tree\Edge;
use Hal\Component\Tree\Node;

/**
 * @group tree
 */
class NodeTest extends \PHPUnit\Framework\TestCase
{
    public function testICanWorkWithNode(): void
    {
        $node = new Node('A');
        $to = new Node('B');
        $edge = new Edge($node, $to);
        $node->addEdge($edge);
        $node->setData('value1');

        $this->assertEquals('value1', $node->getData());
        $this->assertEquals(['B' => $to], $node->getAdjacents());
        $this->assertEquals('A', $node->getKey());
    }
}
