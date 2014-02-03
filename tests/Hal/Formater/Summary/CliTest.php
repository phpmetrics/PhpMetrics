<?php
namespace Test\Hal\Formater\Summary;

use Hal\Formater\Summary\Cli;
use Symfony\Component\Console\Output\ConsoleOutput;


/**
 * @group formater
 */
class CliTest extends \PHPUnit_Framework_TestCase {

    public function testFormaterReturnsHtml() {

        $rs1 = $this->getMockBuilder('\Hal\Result\ResultSet')->disableOriginalConstructor()->getMock();
        $rs1->expects($this->any())->method('asArray')->will($this->returnValue(array('volume' => 5)));
        $rs2 = $this->getMockBuilder('\Hal\Result\ResultSet')->disableOriginalConstructor()->getMock();
        $rs2->expects($this->any())->method('asArray')->will($this->returnValue(array('volume' => 15)));

        $collection = $this->getMockBuilder('\Hal\Result\ResultCollection') ->disableOriginalConstructor() ->getMock();
        $collection->expects($this->any()) ->method('getIterator') ->will($this->returnValue(new \ArrayIterator(array($rs1, $rs2))));
        $collection->expects($this->any()) ->method('asArray') ->will($this->returnValue(array(
                array('volume' => 5)
                , array('volume' => 10)
            )));
        $collection->expects($this->any()) ->method('getFilename') ->will($this->returnValue('abc'));

        $validator = $this->getMockBuilder('\Hal\Rule\Validator')->disableOriginalConstructor()->getMock();


        $output = $this->getMock('\Symfony\Component\Console\Output\OutputInterface');
        $outputFormater = $this->getMock('\Symfony\Component\Console\Formatter\OutputFormatterInterface');
        $output->expects($this->any())->method('getFormatter')->will($this->returnValue($outputFormater));
        $output->expects($this->any())->method('write');

        $formater = new Cli($validator, $output, 1);
        $formater->terminate($collection);
    }

    public function testFormaterHasName() {
        $validator = $this->getMockBuilder('\Hal\Rule\Validator')->disableOriginalConstructor()->getMock();
        $output = $this->getMock('\Symfony\Component\Console\Output\OutputInterface');
        $formater = new Cli($validator, $output, 1);
        $this->assertNotNull($formater->getName());
    }
}