<?php
namespace Test\Hal\Formater\Summary;

use Hal\Bounds\Bounds;
use Hal\Bounds\DirectoryBounds;
use Hal\Formater\Summary\Xml;
use Symfony\Component\Console\Output\ConsoleOutput;


/**
 * @group formater
 */
class XmlTest extends \PHPUnit_Framework_TestCase {

    public function testFormaterReturnsHtml() {

        $rs1 = $this->getMockBuilder('\Hal\Result\ResultSet')->disableOriginalConstructor()->getMock();
        $rs1->expects($this->any())->method('asArray')->will($this->returnValue(array('volume' => 5)));
        $rs1->expects($this->any())->method('getFilename')->will($this->returnValue('path2/file1.php'));
        $rs2 = $this->getMockBuilder('\Hal\Result\ResultSet')->disableOriginalConstructor()->getMock();
        $rs2->expects($this->any())->method('asArray')->will($this->returnValue(array('volume' => 15)));
        $rs2->expects($this->any())->method('getFilename')->will($this->returnValue('path1/file1.php'));

        $collection = $this->getMockBuilder('\Hal\Result\ResultCollection') ->disableOriginalConstructor() ->getMock();
        $collection->expects($this->any()) ->method('getIterator') ->will($this->returnValue(new \ArrayIterator(array($rs1, $rs2))));
        $collection->expects($this->any()) ->method('getFilename') ->will($this->returnValue('abc'));
        $collection->expects($this->any()) ->method('asArray') ->will($this->returnValue(array(
            array('volume' => 5)
            , array('volume' => 15)
        )));

        $validator = $this->getMockBuilder('\Hal\Rule\Validator')->disableOriginalConstructor()->getMock();

        $bounds = new Bounds();
        $agregatedBounds = new DirectoryBounds(2);
        $formater = new Xml($validator, $bounds, $agregatedBounds);
        $output = $formater->terminate($collection);

        $xml = new \SimpleXMLElement($output);
        $p = $xml->xpath('//project');
        $m = $xml->xpath('//project/modules/module[@namespace="path1"]');
        $m = $m[0];
        $this->assertEquals('15', $m['volume']);
        $this->assertEquals('15', $m['volume']);
        $this->assertCount(2, $xml->xpath('//project/modules/module'));

    }

    public function testFormaterHasName() {
        $validator = $this->getMockBuilder('\Hal\Rule\Validator')->disableOriginalConstructor()->getMock();
        $bounds = new Bounds();
        $agregatedBounds = new DirectoryBounds(0);
        $formater = new Xml($validator, $bounds, $agregatedBounds);
        $this->assertNotNull($formater->getName());
    }
}