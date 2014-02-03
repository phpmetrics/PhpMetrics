<?php
namespace Test\Hal\Formater\Details;

use Hal\Formater\Details\Html;


/**
 * @group formater
 */
class HtmlTest extends \PHPUnit_Framework_TestCase {

    public function testFormaterReturnsHtml() {

        $collection = $this->getMockBuilder('\Hal\Result\ResultCollection')
            ->disableOriginalConstructor()
            ->getMock();
        $collection->expects($this->any())
            ->method('asArray')
            ->will($this->returnValue(array(
                array('volume' => 0, 'length' => 100)
            , array('volume' => 10, 'length' => 50)
            )));

        $validator = $this->getMockBuilder('\Hal\Rule\Validator')->disableOriginalConstructor()->getMock();
        $formater = new Html($validator);

        $output = $formater->terminate($collection);
        $this->assertContains('<html>', $output);
    }

    public function testFormaterHasName() {
        $validator = $this->getMockBuilder('\Hal\Rule\Validator')->disableOriginalConstructor()->getMock();
        $formater = new Html($validator, 2);
        $this->assertNotNull($formater->getName());
    }
}