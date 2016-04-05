<?php

namespace Test\Hal\Application\Formater\Details;

use Hal\Component\Bounds\Bounds;
use Hal\Application\Formater\Details\Json;
use Hal\Component\Result\ResultCollection;

/**
 * @group formater
 */
class JsonTest extends \PHPUnit_Framework_TestCase
{
    public function testFormatterReturnsJson()
    {
        // all results
        $rs1 = $this->getMockBuilder('\Hal\Component\Result\ResultSet')->disableOriginalConstructor()->getMock();
        $rs1->expects($this->any())->method('asArray')->will($this->returnValue(array('volume' => 5)));
        $rs1->expects($this->any())->method('getFilename')->will($this->returnValue('path2/file1.php'));
        $rs2 = $this->getMockBuilder('\Hal\Component\Result\ResultSet')->disableOriginalConstructor()->getMock();
        $rs2->expects($this->any())->method('asArray')->will($this->returnValue(array('volume' => 15)));
        $rs2->expects($this->any())->method('getFilename')->will($this->returnValue('path1/file1.php'));

        $collection = $this->getMockBuilder('\Hal\Component\Result\ResultCollection') ->disableOriginalConstructor() ->getMock();
        $collection->expects($this->any()) ->method('getIterator') ->will($this->returnValue(new \ArrayIterator(array($rs1, $rs2))));
        $collection->expects($this->any()) ->method('getFilename') ->will($this->returnValue('abc'));
        $collection->expects($this->any()) ->method('asArray') ->will(
            $this->returnValue(array(
                    array('volume' => 5),
                    array('volume' => 15)
                )
            )
        );

        // grouped results
        $groupedResults = new ResultCollection();

        // formatter
        $formatter = new Json(false, $this->getMockBuilder('\Hal\Application\Extension\ExtensionService')->disableOriginalConstructor()->getMock());
        $output = $formatter->terminate($collection, $groupedResults);


        $json = json_decode($output);

        // valid json
        $this->assertNotNull($json, 'json string is invalid or empty');
        $this->assertEquals('5', $json[0]->volume);
        $this->assertCount(2, $json);
    }

    public function testFormatterHasName() {
        $validator = $this->getMockBuilder('\Hal\Application\Rule\Validator')->disableOriginalConstructor()->getMock();
        $bounds = new Bounds();
        $formatter = new Json(false, $this->getMockBuilder('\Hal\Application\Extension\ExtensionService')->disableOriginalConstructor()->getMock());
        $this->assertNotNull($formatter->getName());
    }
}
