<?php
namespace Test\Hal\Application\Formater\Summary;

use Hal\Component\Bounds\Bounds;
use Hal\Component\Bounds\Result\BoundsResult;
use Hal\Application\Formater\Details\Cli;
use Hal\Component\Result\ResultCollection;
use Symfony\Component\Console\Output\ConsoleOutput;


/**
 * @group formater
 */
class CliTest extends \PHPUnit_Framework_TestCase {

    public function testFormaterReturnsHtml() {

        $rs1 = $this->getMockBuilder('\Hal\Component\Result\ResultSet')->disableOriginalConstructor()->getMock();
        $rs1->expects($this->any())->method('asArray')->will($this->returnValue(array('volume' => 5)));
        $rs2 = $this->getMockBuilder('\Hal\Component\Result\ResultSet')->disableOriginalConstructor()->getMock();
        $rs2->expects($this->any())->method('asArray')->will($this->returnValue(array('volume' => 15)));

        $collection = $this->getMockBuilder('\Hal\Component\Result\ResultCollection') ->disableOriginalConstructor() ->getMock();
        $collection->expects($this->any()) ->method('getIterator') ->will($this->returnValue(new \ArrayIterator(array($rs1, $rs2))));
        $collection->expects($this->any()) ->method('asArray') ->will($this->returnValue(array(
                array('volume' => 5)
                , array('volume' => 10)
            )));
        $collection->expects($this->any()) ->method('getFilename') ->will($this->returnValue('abc'));

        $validator = $this->getMockBuilder('\Hal\Application\Rule\Validator')->disableOriginalConstructor()->getMock();

        $bounds = new Bounds();
        $formater = new Cli($validator, $bounds);
        $groupedResults = new ResultCollection();
        $output = $formater->terminate($collection, $groupedResults);
        $this->assertRegExp('/Maintainability/', $output);
    }

    public function testFormaterHasName() {
        $validator = $this->getMockBuilder('\Hal\Application\Rule\Validator')->disableOriginalConstructor()->getMock();
        $bounds = new Bounds();
        $formater = new Cli($validator, $bounds);
        $this->assertNotNull($formater->getName());
    }
}