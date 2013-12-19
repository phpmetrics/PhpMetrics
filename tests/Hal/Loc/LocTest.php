<?php
namespace Test\Hal\Halstead;

use Hal\Loc\Result;
use Hal\Loc\Loc;

/**
 * @group loc
 * @group metric
 */
class LocTest extends \PHPUnit_Framework_TestCase {

    public function testLocServiceReturnsResult() {


        $object = new Loc();
        $filename = tempnam(sys_get_temp_dir(), 'tmp-unit');
        $this->assertInstanceOf("\Hal\Loc\Result", $object->calculate($filename));
        unlink($filename);
    }

    public function testLocResultCanBeConvertedToArray() {

        $result = new \Hal\Loc\Result();
        $array = $result->asArray();

        $this->assertArrayHasKey('cyclomaticComplexity', $array);
        $this->assertArrayHasKey('loc', $array);
        $this->assertArrayHasKey('logicalLoc', $array);
    }
}