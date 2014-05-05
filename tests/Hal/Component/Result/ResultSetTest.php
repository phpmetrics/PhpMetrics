<?php
namespace Test\Hal\Component\Result;

use Hal\Component\Result\ResultSet;
use Hal\Application\Rule\RuleSet;
use Hal\Application\Rule\Validator;
use Hal\Component\Token\TokenType;

/**
 * @group result
 */
class ResultSetTest extends \PHPUnit_Framework_TestCase {

   public function testResultSetCanBeExportedToArray() {


       $loc = $this->getMock('\Hal\Metrics\Complexity\Text\Length\Result');
       $loc->expects($this->once())->method('asArray')->will($this->returnValue(array('loc' => 100)));

       $hs = $this->getMock('\Hal\Metrics\Complexity\Text\Halstead\Result');
       $hs->expects($this->once())->method('asArray')->will($this->returnValue(array('volume' => 100)));

       $mi = $this->getMock('\Hal\Metrics\Design\Component\MaintenabilityIndex\Result');
       $mi->expects($this->once())->method('asArray')->will($this->returnValue(array('maintenabilityIndex' => 100)));

       $cg = $this->getMockBuilder('\Hal\Metrics\Complexity\Structural\HenryAndKafura\Result')->disableOriginalConstructor()->getMock();
       $cg->expects($this->once())->method('asArray')->will($this->returnValue(array('instability' => 1)));

       $resultset = new ResultSet('my');
       $resultset->setHalstead($hs)->setLoc($loc)->setMaintenabilityIndex($mi)->setCoupling($cg);

       $expected = array(
           'filename' => 'my'
           , 'name' => 'my'
           , 'loc' => 100
           , 'volume' => 100
           , 'maintenabilityIndex' => 100
           , 'instability' => 1
       );

       $this->assertEquals($expected, $resultset->asArray());

   }
}