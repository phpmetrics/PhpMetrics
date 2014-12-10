<?php
namespace Test\Hal\Application\Score;
use Hal\Application\Score\Calculator;


/**
 * @group score
 * @group calculator
 */
class CalculatorTest extends \PHPUnit_Framework_TestCase {

    private $object;

    public function setUp() {
        $this->object = new Calculator();
    }

    /**
     * @dataProvider highProvider
     */
    public function testHighIsBetter($good, $bad, $note, $expected) {
        $this->assertEquals($expected, $this->object->highIsBetter($good, $bad, $note));
    }

    /**
     * @dataProvider lowProvider
     */
    public function testLowIsBetter($good, $bad, $note, $expected) {
        $this->assertEquals($expected, $this->object->lowIsBetter($good, $bad, $note));
    }

    public function highProvider() {
        return array(
            array(105, 65, 65, 12.26)
            , array(105, 65, 85, 36.3)
            , array(105, 65, 105, 50)
        );
    }

    public function lowProvider() {
        return array(

            array(2, 10, 2, 50)
            , array(2, 10, 4, 40)
            , array(2, 10, 8, 20)
            , array(2, 10, 9, 15)
            , array(1800, 24000, 1800, 50)
            , array(1800, 24000, 10000, 32.92)
            , array(1800, 24000, 15000, 22.5
            )
        );
    }
}