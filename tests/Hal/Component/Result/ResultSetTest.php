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

       $mi = $this->getMock('\Hal\Metrics\Design\Component\MaintainabilityIndex\Result');
       $mi->expects($this->once())->method('asArray')->will($this->returnValue(array('maintainabilityIndex' => 100)));

       $cg = $this->getMockBuilder('\Hal\Metrics\Complexity\Structural\HenryAndKafura\Result')->disableOriginalConstructor()->getMock();
       $cg->expects($this->once())->method('asArray')->will($this->returnValue(array('instability' => 1)));

       $resultset = new ResultSet('my');
       $resultset->setHalstead($hs)->setLoc($loc)->setMaintainabilityIndex($mi)->setCoupling($cg);

       $expected = array(
           'filename' => 'my'
           , 'name' => 'my'
           , 'loc' => 100
           , 'volume' => 100
           , 'maintainabilityIndex' => 100
           , 'instability' => 1
       );

       $this->assertEquals($expected, $resultset->asArray());

   }
}