<?php
namespace Test\Hal\Component\Result;

use Hal\Component\Result\ResultCollection;
use Hal\Component\Result\ResultSet;
use Hal\Application\Rule\RuleSet;
use Hal\Application\Rule\Validator;
use Hal\Component\Token\TokenType;

/**
 * @group result
 */
class ResultCollectionTest extends \PHPUnit_Framework_TestCase {

   public function testICanAccessExportResultCollectionToArray() {

       $rs1 = $this->getMockBuilder('\Hal\Component\Result\ResultSet')->disableOriginalConstructor()->getMock();
       $rs1->expects($this->once())->method('asArray')->will($this->returnValue(array('volume' => 5)));
       $rs1->expects($this->once())->method('getName')->will($this->returnValue('f1.php'));
       $rs2 = $this->getMockBuilder('\Hal\Component\Result\ResultSet')->disableOriginalConstructor()->getMock();
       $rs2->expects($this->once())->method('asArray')->will($this->returnValue(array('volume' => 10)));
       $rs2->expects($this->once())->method('getName')->will($this->returnValue('f2.php'));

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
        $rs1 = $this->getMockBuilder('\Hal\Component\Result\ResultSet')->disableOriginalConstructor()->getMock();
        $rs1->expects($this->once())->method('getName')->will($this->returnValue('f1.php'));
        $rs2 = $this->getMockBuilder('\Hal\Component\Result\ResultSet')->disableOriginalConstructor()->getMock();

        $collection = new ResultCollection();
        $collection ->push($rs1);
        $collection['f2.php'] = $rs2;


        $i = 0;
        foreach($collection as $n) {
            $i++;
        }

        $this->assertEquals(2, $i);
        $this->assertEquals(2, sizeof($collection));
        $this->assertEquals($rs1, $collection['f1.php']);

        unset($collection['f2.php']);
        $this->assertEquals(1, sizeof($collection));
        $this->assertFalse(isset($collection['f2.php']));
    }
}