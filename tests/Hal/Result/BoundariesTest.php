<?php
namespace Test\Hal\Result;
use Hal\Result\ResultBoundary;
use Hal\Result\ResultCollection;
use Hal\Result\ResultSet;


/**
 * @group result
 */
class BoundariesTest extends \PHPUnit_Framework_TestCase {

    public function testICanGetBoundaries() {

        $collection = new ResultCollection();


        $collection = $this->getMockBuilder('\Hal\Result\ResultCollection')
            ->disableOriginalConstructor()
            ->getMock();
        $collection->expects($this->once())
            ->method('asArray')
            ->will($this->returnValue(array(
                 array('volume' => 0, 'length' => 100)
                , array('volume' => 10, 'length' => 50)
            )));

        $boundaries = new ResultBoundary($collection);

        $this->assertEquals(0, $boundaries->getMin('volume'));
        $this->assertEquals(5, $boundaries->getAverage('volume'));
        $this->assertEquals(10, $boundaries->getMax('volume'));
        $this->assertEquals(150, $boundaries->getSum('length'));
    }
}