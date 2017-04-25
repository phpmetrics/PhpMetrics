<?php

namespace Test;

use Hal\Component\Tree\Graph;
use Hal\Component\Tree\GraphFactory;
use Hal\Component\Tree\Node;
use Hal\Component\Tree\Operator\SizeOfTree;

/**
 * @group tree
 */
class SizeOfTreeTest extends \PHPUnit_Framework_TestCase
{

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

    public function testICanGetInfoAboutAverageHeightOfTree()
    {
        $graph = new Graph();
        $a = new Node('A');
        $b = new Node('B');
        $c = new Node('C');
        $d = new Node('D');
        $e = new Node('E');
        $f = new Node('F');
        $g = new Node('G');
        $x = new Node('X');
        $y = new Node('Y');
        $z = new Node('Z');

        $graph->insert($a)->insert($b)->insert($c)->insert($d)->insert($e)->insert($f)->insert($g);
        $graph->insert($x)->insert($y)->insert($z);

        // case 1
        $graph->addEdge($a, $b); // A -> B
        $graph->addEdge($b, $c); // B -> C
        $graph->addEdge($c, $d); // C -> D
        $graph->addEdge($c, $f); // C -> F
        $graph->addEdge($f, $g); // F -> G
        $graph->addEdge($a, $e); // A -> E
        $graph->addEdge($x, $y); // X -> Y

        // longest branch A = 5      A -> B -> C -> F -> G
        // longest branch X = 2      X -> Y
        // longest branch Z = 1      Z

        $size = new SizeOfTree($graph);

        $this->assertEquals(5, $size->getLongestBranch($a));
        $this->assertEquals(2, $size->getLongestBranch($x));
        $this->assertEquals(1, $size->getLongestBranch($z));

        $this->assertEquals(2.67, $size->getAverageHeightOfGraph());
    }
}
