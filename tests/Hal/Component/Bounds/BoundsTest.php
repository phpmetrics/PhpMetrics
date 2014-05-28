<?php
namespace Test\Hal\Component\Bounds;
use Hal\Component\Bounds\Bounds;


/**
 * @group bounds
 */
class BoundsTest extends \PHPUnit_Framework_TestCase {

    public function testICanGetBoundsFromCollection() {

        $collection = $this->getMockBuilder('\Hal\Component\Result\ResultCollection')
            ->disableOriginalConstructor()
            ->getMock();
        $collection->expects($this->any())
            ->method('asArray')
            ->will($this->returnValue(array(
                array('volume' => 0, 'length' => 100)
            , array('volume' => 10, 'length' => 50)
            )));

        $bounds = new Bounds();
        $result = $bounds->calculate($collection);

        $this->assertInstanceOf('\Hal\Component\Bounds\Result\BoundsResult', $result);

        $this->assertEquals(0, $result->getMin('volume'));
        $this->assertEquals(5, $result->getAverage('volume'));
        $this->assertEquals(10, $result->getMax('volume'));
        $this->assertEquals(150, $result->getSum('length'));
    }
}