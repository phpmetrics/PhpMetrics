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
     * @group wip
     */
    public function testHighIsBetter($good, $bad, $note, $expected) {
        $this->assertEquals($expected, $this->object->highIsBetter($good, $bad, $note));
    }

    /**
     * @dataProvider lowProvider
     */
    public function testLowIsBetter($good, $bad, $note, $expected) {
        $this->object->lowIsBetter($good, $bad, $note);
        $this->assertEquals($expected, $this->object->lowIsBetter($good, $bad, $note));
    }

    public function highProvider() {
        return array(
            array(30, 10, 31, 100)
            , array(30, 10, 30, 100)
            , array(30, 10, 25, 75)
            , array(30, 10, 20, 50)
            , array(30, 10, 15, 25)
            , array(3, 10, 10, 0)
            , array(30, 10, 9, 0)
        );
    }

    public function lowProvider() {
        return array(
           array(10,    30, 9   , 100)
           , array(10,  20, 10  , 100)
           , array(10,  30, 15  , 75)
           , array(10,  30, 20  , 50)
           , array(10,  30, 25  , 25)
           , array(10,  30, 30  , 0)
           , array(10,  30, 31  , 0)
        );
    }
}