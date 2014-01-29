<?php
namespace Test\Hal\Formater;

use Hal\Formater\Html;


/**
 * @group formater
 */
class HtmlTest extends \PHPUnit_Framework_TestCase {

    public function testFormaterReturnsHtml() {

        $resultset = $this->getMockBuilder('\Hal\Result\ResultSet')
            ->disableOriginalConstructor()
            ->getMock();
        $resultset ->expects($this->any())
            ->method('asArray')
            ->will($this->returnValue(array()));

        $formater = new Html();
        $formater->pushResult($resultset);

        $output = $formater->terminate();
        $this->assertContains('<html>', $output);
    }
}