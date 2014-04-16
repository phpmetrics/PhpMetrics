<?php
namespace Test\Hal\Metrics\Complexity\Text\Halstead;

use Hal\Metrics\Complexity\Component\Myer\Myer;

/**
 * @group myer
 * @group metric
 */
class MyerTest extends \PHPUnit_Framework_TestCase {

    /**
     * @dataProvider provideIntervals
     */
    public function testICanGetMyerInterval($filename, $interval, $distance) {

        $object = new Myer(new \Hal\Component\Token\Tokenizer());
        $result = $object->calculate($filename);
        $this->assertEquals($interval, $result->getInterval());
        $this->assertEquals($distance, $result->getDistance());
    }

    public function provideIntervals() {
        return array(
            array(__DIR__.'/../../../resources/myer/f1.php', '3:4', 1)
            , array(__DIR__.'/../../../resources/myer/f2.php', '8:15', 7)
        );
    }

    public function testMyerResultCanBeConvertedToArray() {

        $result = new \Hal\Metrics\Complexity\Component\Myer\Result();
        $array = $result->asArray();
        $this->assertArrayHasKey('myerInterval', $array);
        $this->assertArrayHasKey('myerDistance', $array);
    }
}