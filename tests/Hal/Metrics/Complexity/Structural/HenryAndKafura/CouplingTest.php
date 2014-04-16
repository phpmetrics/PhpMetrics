<?php
namespace Test\Hal\Metrics\Complexity\Structural\HenryAndKafura;
use Hal\Metrics\Complexity\Structural\HenryAndKafura\Coupling;

/**
 * @group coupling
 */
class CouplingTest extends \PHPUnit_Framework_TestCase {

    private $result;

    public function setup() {
        $classesInfos = array(
            '\Ns1\Class1' => array('\Ns1\Class2', '\Ns2\Class1')
        , '\Ns1\Class2' => array('\Ns2\Class1')
        , '\Ns2\Class1' => array()
        );
        $classes = array();
        foreach($classesInfos as $classname => $dependencies) {
            $class = $this->getMockBuilder('\Hal\Component\OOP\Reflected\ReflectedClass')->disableOriginalConstructor()->getMock();
            $class->expects($this->any())->method('getName')->will($this->returnValue($classname));
            $class->expects($this->once())->method('getDependencies')->will($this->returnValue($dependencies));
            array_push($classes, $class);
        }

        $result = $this->getMock('\Hal\Component\OOP\Extractor\Result');
        $result->expects($this->any())->method('getClasses')->will($this->returnValue($classes));
        $results = array($result);

        $classMap = $this->getMockBuilder('\Hal\Component\OOP\Extractor\ClassMap')->disableOriginalConstructor()->getMock();
        $classMap->expects($this->once())->method('all')->will($this->returnValue($results));

        $coupling = new Coupling();
        $this->result = $coupling->calculate($classMap);
    }
    
    
   public function testICanGetInfoAboutCoupling() {
       $this->assertEquals(2, $this->result->get('\Ns1\Class1')->getEfferentCoupling());
       $this->assertEquals(0, $this->result->get('\Ns1\Class1')->getAfferentCoupling());
       $this->assertEquals(1, $this->result->get('\Ns1\Class1')->getInstability());

       $this->assertEquals(1, $this->result->get('\Ns1\Class2')->getEfferentCoupling());
       $this->assertEquals(1, $this->result->get('\Ns1\Class2')->getAfferentCoupling());
       $this->assertEquals(0.5, $this->result->get('\Ns1\Class2')->getInstability());

       $this->assertEquals(0, $this->result->get('\Ns2\Class1')->getEfferentCoupling());
       $this->assertEquals(2, $this->result->get('\Ns2\Class1')->getAfferentCoupling());
       $this->assertEquals(0, $this->result->get('\Ns2\Class1')->getInstability());
   }

    public function testICanExportCoupling() {
        $expected = array(
            'instability' => 1
            , 'afferentCoupling' => 0
            , 'efferentCoupling' => 2
        );
        $this->assertEquals($expected, $this->result->get('\Ns1\Class1')->asArray());
    }
}