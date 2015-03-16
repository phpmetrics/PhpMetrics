<?php
namespace Test\Hal\Application\Score\Factor;
use Hal\Application\Score\Calculator;
use Hal\Application\Score\Factor\MaintainabilityFactor;


/**
 * @group score
 */
class MaintainabilityFactorTest extends \PHPUnit_Framework_TestCase {

    private $object;

    public function setUp() {
        $this->object = new MaintainabilityFactor(new Calculator());
    }

    /**
     * @dataProvider provider
     */
    public function testCalculateMaintainability($expected, $maintainability) {

        $groupedResults = $this->getMockBuilder('\Hal\Component\Result\ResultCollection')->disableOriginalConstructor()->getMock();
        $collection = $this->getMockBuilder('\Hal\Component\Result\ResultCollection')->disableOriginalConstructor()->getMock();

        $bound = $this->getMockBuilder('\Hal\Component\Bounds\Result\ResultInterface')->disableOriginalConstructor()->getMock();
        $bound->expects($this->any())->method('getAverage')->will($this->returnValue($maintainability));

        $score = $this->object->calculate($collection, $groupedResults, $bound);
        $this->assertEquals($expected, $score);
    }

    public function provider() {
        return array(
            array( 10.42,  65)
            , array(52.08, 85)
            , array(100, 110)
        );
    }
}