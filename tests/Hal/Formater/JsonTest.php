<?php
namespace Test\Hal\Formater;

use Hal\Formater\Details\Json;


/**
 * @group formater
 */
class JsonTest extends \PHPUnit_Framework_TestCase {

    public function testFormaterReturnsJson() {

        $collection = $this->getMockBuilder('\Hal\Result\ResultCollection')
            ->disableOriginalConstructor()
            ->getMock();
        $collection->expects($this->once())
            ->method('asArray')
            ->will($this->returnValue(array(
                array('volume' => 0, 'length' => 100)
            , array('volume' => 10, 'length' => 50)
            )));

        $formater = new Json();

        $output = $formater->terminate($collection);
        $this->assertTrue(is_array(json_decode($output)), 'output is valid json');
    }
}