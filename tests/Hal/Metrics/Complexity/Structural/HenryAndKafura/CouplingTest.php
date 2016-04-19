<?php
namespace Test\Hal\Metrics\Complexity\Structural\HenryAndKafura;
use Hal\Component\OOP\Extractor\ClassMap;
use Hal\Component\OOP\Extractor\Extractor;
use Hal\Component\Token\Tokenizer;
use Hal\Component\Tree\Edge;
use Hal\Component\Tree\Node;
use Hal\Metrics\Complexity\Structural\HenryAndKafura\Coupling;
use Hal\Metrics\Complexity\Structural\HenryAndKafura\Result;

/**
 * @group coupling
 */
class CouplingTest extends \PHPUnit_Framework_TestCase {


    public function testICanGetInformationsABoutCouplingOfClass() {

        $nodeA = new Node('A');
        $nodeA->addEdge(new Edge(new Node('A'), new Node('B'))); // A -> B
        $nodeA->addEdge(new Edge(new Node('A'), new Node('C'))); // A -> C
        $nodeA->addEdge(new Edge(new Node('A'), new Node('D'))); // A -> D
        $nodeA->addEdge(new Edge(new Node('E'), new Node('A'))); // E -> A

        $coupling = new Coupling();
        $result = $coupling->calculate($nodeA);

        $this->assertEquals(3, $result->getAfferentCoupling());
        $this->assertEquals(1, $result->getEfferentCoupling());
        $this->assertEquals(.25, $result->getInstability());
    }

    public function testCouplingResultCanBeConvertedToArray() {

        $result = new Result;
        $array = $result->asArray();
        $this->assertArrayHasKey('efferentCoupling', $array);
        $this->assertArrayHasKey('afferentCoupling', $array);
        $this->assertArrayHasKey('instability', $array);
    }
}
