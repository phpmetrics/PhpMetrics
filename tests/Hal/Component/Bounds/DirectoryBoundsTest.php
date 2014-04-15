<?php
namespace Test\Hal\Component\Bounds;
use Hal\Component\Bounds\Bounds;
use Hal\Component\Bounds\DirectoryBounds;


/**
 * @group bounds
 */
class DirectoryBoundsTest extends \PHPUnit_Framework_TestCase {

    public function testICanGetBoundsForDirectory() {

        $rs1 = $this->getMockBuilder('\Hal\Component\Result\ResultSet')->disableOriginalConstructor()->getMock();
        $rs1->expects($this->any())->method('getFilename')->will($this->returnValue('folder1/folder2/file1.php'));
        $rs1->expects($this->any())->method('asArray')->will($this->returnValue(array('volume' => 10)));

        $rs2 = $this->getMockBuilder('\Hal\Component\Result\ResultSet')->disableOriginalConstructor()->getMock();
        $rs2->expects($this->any())->method('getFilename')->will($this->returnValue('folder1/folder2/file2.php'));
        $rs2->expects($this->any())->method('asArray')->will($this->returnValue(array('volume' => 20)));

        $rs3 = $this->getMockBuilder('\Hal\Component\Result\ResultSet')->disableOriginalConstructor()->getMock();
        $rs3->expects($this->any())->method('getFilename')->will($this->returnValue('folder3/file1.php'));
        $rs3->expects($this->any())->method('asArray')->will($this->returnValue(array('volume' => 10)));

        $rs4 = $this->getMockBuilder('\Hal\Component\Result\ResultSet')->disableOriginalConstructor()->getMock();
        $rs4->expects($this->any())->method('getFilename')->will($this->returnValue('folder1/folder2/file3.php'));
        $rs4->expects($this->any())->method('asArray')->will($this->returnValue(array('volume' => 30)));



        $collection = $this->getMockBuilder('\Hal\Component\Result\ResultCollection')->disableOriginalConstructor()->getMock();
        $collection->expects($this->any())
            ->method('getIterator')
            ->will($this->returnValue(new \ArrayIterator(array($rs1, $rs2, $rs3, $rs4))));

        $bounds = new DirectoryBounds();
        $results = $bounds->calculate($collection);

        $this->assertTrue(is_array($results));
        $this->assertArrayHasKey('folder1', $results);

        $result = $results['folder1'];
        $this->assertInstanceOf('\Hal\Component\Bounds\Result\DirectoryResult', $result);

        $this->assertEquals(10, $result->getMin('volume'));
        $this->assertEquals(20, $result->getAverage('volume'));
        $this->assertEquals(30, $result->getMax('volume'));
    }
}