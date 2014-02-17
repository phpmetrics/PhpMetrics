<?php
namespace Test\Hal\Coupling;
use Hal\Coupling\Coupling;

/**
 * @group coupling
 */
class CouplingTest extends \PHPUnit_Framework_TestCase {


   public function testICanGetInfoABoutCoupling() {

       $classesInfos = array(
            '\Ns1\Class1' => array('\Ns1\Class2', '\Ns2\Class1')
            , '\Ns1\Class2' => array('\Ns2\Class1')
            , '\Ns2\Class1' => array()
       );
       $classes = array();
       foreach($classesInfos as $classname => $dependencies) {
           $class = $this->getMockBuilder('\Hal\OOP\Reflected\ReflectedClass')->disableOriginalConstructor()->getMock();
           $class->expects($this->any())->method('getName')->will($this->returnValue($classname));
           $class->expects($this->once())->method('getDependencies')->will($this->returnValue($dependencies));
           array_push($classes, $class);
       }

       $result = $this->getMock('\Hal\OOP\Extractor\Result');
       $result->expects($this->any())->method('getClasses')->will($this->returnValue($classes));
       $results = array($result);

       $classMap = $this->getMockBuilder('\Hal\OOP\Extractor\ClassMap')->disableOriginalConstructor()->getMock();
       $classMap->expects($this->once())->method('all')->will($this->returnValue($results));

       $coupling = new Coupling();
       $r = $coupling->calculate($classMap);

       $this->assertEquals(2, $r->get('\Ns1\Class1')->getEfferentCoupling());
       $this->assertEquals(0, $r->get('\Ns1\Class1')->getAfferentCoupling());
       $this->assertEquals(1, $r->get('\Ns1\Class1')->getInstability());

       $this->assertEquals(1, $r->get('\Ns1\Class2')->getEfferentCoupling());
       $this->assertEquals(1, $r->get('\Ns1\Class2')->getAfferentCoupling());
       $this->assertEquals(0.5, $r->get('\Ns1\Class2')->getInstability());

       $this->assertEquals(0, $r->get('\Ns2\Class1')->getEfferentCoupling());
       $this->assertEquals(2, $r->get('\Ns2\Class1')->getAfferentCoupling());
       $this->assertEquals(0, $r->get('\Ns2\Class1')->getInstability());

   }
}