<?php
namespace Test\Hal\Metrics\Mood\Instability;
use Hal\Component\Result\ResultCollection;
use Hal\Component\Tree\Graph;
use Hal\Component\Tree\Node;
use Hal\Metrics\Mood\Abstractness\Abstractness;
use Hal\Metrics\Mood\Abstractness\Result;

/**
 * @group mood
 * @group abstractness
 * @group metric
 */
class AbstractnessTest extends \PHPUnit_Framework_TestCase {

    public function testICanKnowTheAbstractnessOfPackage() {

        $class = $this->getMock('\\Hal\\Component\\Reflected\\Klass');
        $class->method('isInterface')->will($this->returnValue(false));

        $interface = $this->getMock('\\Hal\\Component\\Reflected\\Klass');
        $interface->method('isInterface')->will($this->returnValue(true));

        $abstract = $this->getMock('\\Hal\\Component\\Reflected\\Klass');
        $abstract->method('isAbstract')->will($this->returnValue(true));



        $graph = new Graph();
        $graph
            ->insert(new Node('c1', $class))
            ->insert(new Node('c2', $class))
            ->insert(new Node('c3', $class))
            ->insert(new Node('i1', $interface))
            ->insert(new Node('a1', $abstract))
        ;

        $abstractness = new Abstractness();
        $result = $abstractness->calculate($graph);
        $this->assertEquals(.4, $result->getAbstractness());
    }

    public function testAbstractnessResultCanBeConvertedToArray() {
        $result = new Result();
        $array = $result->asArray();
        $this->assertArrayHasKey('abstractness', $array);
    }

}