<?php
namespace Test\Hal\Metrics\Mood\Instability;
use Hal\Component\Result\ResultCollection;
use Hal\Component\Tree\Edge;
use Hal\Component\Tree\Graph;
use Hal\Component\Tree\Node;
use Hal\Metrics\Mood\Instability\Instability;
use Hal\Metrics\Mood\Instability\Result;

/**
 * @group mood
 * @group instability
 * @group metric
 */
class InstabilityTest extends \PHPUnit_Framework_TestCase {


    public function testICanKnowTheInstabilityOfPackage() {

        $nodeA = new Node('A');
        $nodeB = new Node('B');
        $nodeC = new Node('C');
        $nodeD = new Node('D');
        $nodeE = new Node('E');
        $nodeA->addEdge(new Edge($nodeA, $nodeA)); // A -> B
        $nodeA->addEdge(new Edge($nodeA, $nodeC)); // A -> C
        $nodeA->addEdge(new Edge($nodeA, $nodeD)); // A -> D
        $nodeA->addEdge(new Edge($nodeE, $nodeA)); // E -> A

        $graph = new Graph();
        $graph->insert($nodeA)->insert($nodeB)->insert($nodeC)->insert($nodeD)->insert($nodeE);

        $instability = new Instability();
        $result = $instability->calculate($graph);

        $this->assertEquals(.4, $result->getInstability());
    }

    public function testInstabilityResultCanBeConvertedToArray() {
        $result = new Result();
        $array = $result->asArray();
        $this->assertArrayHasKey('instability', $array);
    }

}