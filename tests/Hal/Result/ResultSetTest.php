<?php
namespace Test\Hal\Result;

use Hal\Result\ResultSet;
use Hal\Rule\RuleSet;
use Hal\Rule\Validator;
use Hal\Token\TokenType;

/**
 * @group result
 */
class ResultSetTest extends \PHPUnit_Framework_TestCase {

   public function testResultSetCanBeExportedToArray() {


       $loc = $this->getMock('\Hal\Loc\Result');
       $loc->expects($this->once())->method('asArray')->will($this->returnValue(array('loc' => 100)));

       $hs = $this->getMock('\Hal\Halstead\Result');
       $hs->expects($this->once())->method('asArray')->will($this->returnValue(array('volume' => 100)));

       $mi = $this->getMock('\Hal\MaintenabilityIndex\Result');
       $mi->expects($this->once())->method('asArray')->will($this->returnValue(array('maintenabilityIndex' => 100)));

       $resultset = new ResultSet('my');
       $resultset->setHalstead($hs)->setLoc($loc)->setMaintenabilityIndex($mi);

       $expected = array(
           'filename' => 'my'
           , 'loc' => 100
           , 'volume' => 100
           , 'maintenabilityIndex' => 100
       );

       $this->assertEquals($expected, $resultset->asArray());

   }
}