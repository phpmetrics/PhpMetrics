<?php

namespace Test;
use Hal\Component\Tree\Edge;
use Hal\Component\Tree\Graph;
use Hal\Component\Tree\GraphFactory;
use Hal\Component\Tree\HashMap;
use Hal\Component\Tree\Node;
use Hal\Component\Tree\Operator\CycleDetector;
use Hal\Component\Tree\Operator\SizeOfTree;

/**
 * @group tree
 */
class SizeOfTreeTest extends \PHPUnit_Framework_TestCase {

    /**
     * @expectedException  LogicException
     * @expectedExceptionMessage Cannot get size informations of cyclic graph
     */
    public function testICannotGetInfoAboutGraphWhenItIsCyclic()
    {
        $graph = new Graph();
        $a = new Node('A');
        $b = new Node('B');

        $graph->insert($a)->insert($b);

        // case 1
        $graph->addEdge($a, $b); // A -> B
        $graph->addEdge($b, $a); // B -> C

        $size = new SizeOfTree($graph);

    }
    public function testICanGetDepthOfNode()
    {
        $graph = new Graph();
        $a = new Node('A');
        $b = new Node('B');
        $c = new Node('C');
        $d = new Node('D');
        $e = new Node('E');

        $graph->insert($a)->insert($b)->insert($c)->insert($d)->insert($e);

        // case 1
        $graph->addEdge($a, $b); // A -> B
        $graph->addEdge($b, $c); // B -> C
        $graph->addEdge($c, $d); // C -> D
        $graph->addEdge($a, $e); // A -> E  (node with multiple childs)


        $size = new SizeOfTree($graph);

        $this->assertEquals(0, $size->getDepthOfNode($a));
        $this->assertEquals(1, $size->getDepthOfNode($b));
        $this->assertEquals(2, $size->getDepthOfNode($c));
        $this->assertEquals(3, $size->getDepthOfNode($d));
        $this->assertEquals(1, $size->getDepthOfNode($e));
    }

    public function testICanGetNbChildsOfNode()
    {
        $graph = new Graph();
        $a = new Node('A');
        $b = new Node('B');
        $c = new Node('C');
        $d = new Node('D');
        $e = new Node('E');

        $graph->insert($a)->insert($b)->insert($c)->insert($d)->insert($e);

        // case 1
        $graph->addEdge($a, $b); // A -> B
        $graph->addEdge($b, $c); // B -> C
        $graph->addEdge($c, $d); // C -> D
        $graph->addEdge($a, $e); // A -> E  (node with multiple childs)


        $size = new SizeOfTree($graph);

        $this->assertEquals(4, $size->getNumberOfChilds($a));
        $this->assertEquals(2, $size->getNumberOfChilds($b));
        $this->assertEquals(1, $size->getNumberOfChilds($c));
        $this->assertEquals(0, $size->getNumberOfChilds($d));
        $this->assertEquals(0, $size->getNumberOfChilds($e));
    }
}