<?php
namespace Test\Hal\Halstead;

use Hal\Halstead\Halstead;
use Hal\Halstead\Result;

/**
 * @group halstead
 * @group metric
 */
class HalsteadTest extends \PHPUnit_Framework_TestCase {

    public function testHalsteadServiceReturnsResult() {

        $tokenType = $this->getMock('\Hal\Token\TokenType');
        $tokenType->expects($this->any())
            ->method('isOperand')
            ->will($this->returnValue(true));

        $object = new Halstead($tokenType);
        $filename = tempnam(sys_get_temp_dir(), 'tmp-unit');
        $this->assertInstanceOf("\Hal\Halstead\Result", $object->calculate($filename));
        unlink($filename);
    }

    public function testHalsteadResultCanBeConvertedToArray() {

        $result = new Result();
        $array = $result->asArray();

        $this->assertArrayHasKey('volume', $array);
        $this->assertArrayHasKey('length', $array);
        $this->assertArrayHasKey('vocabulary', $array);
        $this->assertArrayHasKey('effort', $array);
        $this->assertArrayHasKey('difficulty', $array);
        $this->assertArrayHasKey('time', $array);
        $this->assertArrayHasKey('bugs', $array);
    }
}