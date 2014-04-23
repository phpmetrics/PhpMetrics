<?php
namespace Test\Hal\Application\Formater\Details;

use Hal\Application\Formater\Details\Html;
use Hal\Component\Result\ResultCollection;


/**
 * @group formater
 */
class HtmlTest extends \PHPUnit_Framework_TestCase {

    public function testFormaterReturnsHtml() {

        $collection = $this->getMockBuilder('\Hal\Component\Result\ResultCollection')
            ->disableOriginalConstructor()
            ->getMock();
        $collection->expects($this->any())
            ->method('asArray')
            ->will($this->returnValue(array(
                array('volume' => 0, 'length' => 100)
            , array('volume' => 10, 'length' => 50)
            )));

        $validator = $this->getMockBuilder('\Hal\Application\Rule\Validator')->disableOriginalConstructor()->getMock();
        $formater = new Html($validator);
        $groupedCollection = new ResultCollection();
        $output = $formater->terminate($collection, $groupedCollection);
        $this->assertContains('<html>', $output);
    }

    public function testFormaterHasName() {
        $validator = $this->getMockBuilder('\Hal\Application\Rule\Validator')->disableOriginalConstructor()->getMock();
        $formater = new Html($validator, 2);
        $this->assertNotNull($formater->getName());
    }
}