<?php
namespace Test\Hal\Result;

use Hal\Result\ResultCollection;
use Hal\Result\ResultSet;
use Hal\Rule\RuleSet;
use Hal\Rule\Validator;
use Hal\Token\TokenType;

/**
 * @group result
 */
class ResultCollectionTest extends \PHPUnit_Framework_TestCase {

   public function testICanAccessExportResultCollectionToArray() {

       $rs1 = $this->getMockBuilder('\Hal\Result\ResultSet')->disableOriginalConstructor()->getMock();
       $rs1->expects($this->once())->method('asArray')->will($this->returnValue(array('volume' => 5)));
       $rs2 = $this->getMockBuilder('\Hal\Result\ResultSet')->disableOriginalConstructor()->getMock();
       $rs2->expects($this->once())->method('asArray')->will($this->returnValue(array('volume' => 10)));

       $collection = new ResultCollection();
       $collection
           ->push($rs1)
           ->push($rs2);

       $expected = array(
           array('volume' => 5)
           , array('volume' => 10)
       );

       $this->assertEquals($expected, $collection->asArray());
   }

    public function testICanAcessToResultCollectionAsAnArray() {
        $rs1 = $this->getMockBuilder('\Hal\Result\ResultSet')->disableOriginalConstructor()->getMock();
        $rs2 = $this->getMockBuilder('\Hal\Result\ResultSet')->disableOriginalConstructor()->getMock();

        $collection = new ResultCollection();
        $collection ->push($rs1);
        $collection[1] = $rs2;


        $i = 0;
        foreach($collection as $n) {
            $i++;
        }


        $this->assertEquals(2, $i);
        $this->assertEquals(2, sizeof($collection));
        $this->assertEquals($rs1, $collection[0]);

        unset($collection[1]);
        $this->assertEquals(1, sizeof($collection));
        $this->assertFalse(isset($collection[1]));
    }
}