<?php
namespace Test\Hal\Application\Formater\Summary;

use Hal\Application\Config\TemplateConfiguration;
use Hal\Component\Bounds\Bounds;
use Hal\Application\Formater\Summary\Html;
use Hal\Component\Result\ResultCollection;


/**
 * @group formater
 */
class HtmlTest extends \PHPUnit_Framework_TestCase {

    public function testFormaterReturnsHtml() {

        $rs1 = $this->getMockBuilder('\Hal\Component\Result\ResultSet')->disableOriginalConstructor()->getMock();
        $rs1->expects($this->any())->method('asArray')->will($this->returnValue(array('volume' => 5)));
        $rs2 = $this->getMockBuilder('\Hal\Component\Result\ResultSet')->disableOriginalConstructor()->getMock();
        $rs2->expects($this->any())->method('asArray')->will($this->returnValue(array('volume' => 15)));

        $collection = $this->getMockBuilder('\Hal\Component\Result\ResultCollection') ->disableOriginalConstructor() ->getMock();
        $collection->expects($this->any()) ->method('getIterator') ->will($this->returnValue(new \ArrayIterator(array(
                $rs1
                , $rs2
            ))));
        $collection->expects($this->any()) ->method('asArray') ->will($this->returnValue(array(
                array('volume' => 5)
                , array('volume' => 10)
            )));
        $collection->expects($this->any()) ->method('getFilename') ->will($this->returnValue('abc'));
        $collection->expects($this->any()) ->method('getScore') ->will($this->returnValue(new \Hal\Application\Score\Result()));

        $validator = $this->getMockBuilder('\Hal\Application\Rule\Validator')->disableOriginalConstructor()->getMock();
        $bounds = new Bounds();
        $formater = new Html($validator, $bounds, new TemplateConfiguration());

        $groupedResults = new ResultCollection();
        $output = $formater->terminate($collection, $groupedResults);
        $this->assertContains('<html>', $output);
    }

    public function testFormaterHasName() {
        $validator = $this->getMockBuilder('\Hal\Application\Rule\Validator')->disableOriginalConstructor()->getMock();
        $bounds = new Bounds();
        $formater = new Html($validator, $bounds, new TemplateConfiguration());
        $this->assertNotNull($formater->getName());
    }
}