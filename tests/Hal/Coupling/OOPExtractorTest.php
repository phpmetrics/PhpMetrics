<?php
namespace Test\Hal\Coupling;

use Hal\Coupling\Coupling;
use Hal\MaintenabilityIndex\MaintenabilityIndex;
use Hal\MaintenabilityIndex\Result;

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
       $result->expects($this->once())->method('getClasses')->will($this->returnValue($classes));

       $coupling = new Coupling();
       $r = $coupling->calculate($result);

       $this->assertEquals(2, $r->getEfferentCoupling('\Ns1\Class1'));
       $this->assertEquals(0, $r->getAfferentCoupling('\Ns1\Class1'));
       $this->assertEquals(1, $r->getInstability('\Ns1\Class1'));

       $this->assertEquals(1, $r->getEfferentCoupling('\Ns1\Class2'));
       $this->assertEquals(1, $r->getAfferentCoupling('\Ns1\Class2'));
       $this->assertEquals(0.5, $r->getInstability('\Ns1\Class2'));

       $this->assertEquals(0, $r->getEfferentCoupling('\Ns2\Class1'));
       $this->assertEquals(2, $r->getAfferentCoupling('\Ns2\Class1'));
       $this->assertEquals(0, $r->getInstability('\Ns2\Class1'));

   }
}