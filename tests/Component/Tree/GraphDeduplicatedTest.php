<?php

namespace Test;

use Hal\Component\Tree\Graph;
use Hal\Component\Tree\GraphDeduplicated;
use Hal\Component\Tree\Node;

/**
 * @group tree
 */
class GraphDeduplicatedTest extends \PHPUnit\Framework\TestCase
{

    public function testEdgeDeduplication()
    {
        $graph = new GraphDeduplicated();
        $a = new Node('A');
        $b = new Node('B');
        $graph->insert($a);
        $graph->insert($b);

        $graph->addEdge($a, $b);
        $graph->addEdge($a, $b);
        $this->assertCount(1, $graph->getEdges());

        $graph->addEdge($b, $a);
        $this->assertCount(2, $graph->getEdges());
    }
}
