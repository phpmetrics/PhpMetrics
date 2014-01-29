<?php
namespace Test\Hal\Formater;

use Hal\Formater\Json;


/**
 * @group formater
 */
class JsonTest extends \PHPUnit_Framework_TestCase {

    public function testFormaterReturnsJson() {

        $resultset = $this->getMockBuilder('\Hal\Result\ResultSet')
            ->disableOriginalConstructor()
            ->getMock();
        $resultset ->expects($this->any())
            ->method('asArray')
            ->will($this->returnValue(array()));

        $resultset ->expects($this->any())
            ->method('getFilename')
            ->will($this->returnValue('myFilename'));

        $formater = new Json();
        $formater->pushResult($resultset);

        $output = $formater->terminate();
        $this->assertInstanceOf('\StdClass', json_decode($output), 'output is valid json');
    }
}