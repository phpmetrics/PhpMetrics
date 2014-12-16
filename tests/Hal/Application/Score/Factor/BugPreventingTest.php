<?php
namespace Test\Hal\Application\Score\Factor;
use Hal\Application\Score\Calculator;
use Hal\Application\Score\Factor\BugPreventingFactor;


/**
 * @group score
 */
class BugPreventingTest extends \PHPUnit_Framework_TestCase {

    private $object;

    public function setUp() {
        $this->object = new BugPreventingFactor(new Calculator());
    }

    /**
     * @dataProvider provider
     */
    public function testCalculateBugPreventing($expected, $note) {

        $groupedResults = $this->getMockBuilder('\Hal\Component\Result\ResultCollection')->disableOriginalConstructor()->getMock();
        $collection = $this->getMockBuilder('\Hal\Component\Result\ResultCollection')->disableOriginalConstructor()->getMock();

        $bound = $this->getMockBuilder('\Hal\Component\Bounds\Result\ResultInterface')->disableOriginalConstructor()->getMock();
        $bound->expects($this->any())->method('getAverage')->will($this->returnValue($note));

        $score = $this->object->calculate($collection, $groupedResults, $bound);
        $this->assertEquals($expected, $score);
    }

    public function provider() {
        return array(
            array( 32.79,  0.5)
            , array(65.57, 0.3)
            , array(100, 0.09)
        );
    }
}