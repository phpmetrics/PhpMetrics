<?php
namespace Test\Hal\Application\Score\Factor;
use Hal\Application\Score\Calculator;
use Hal\Application\Score\Factor\MaintenabilityFactor;


/**
 * @group score
 */
class MaintenabilityFactorTest extends \PHPUnit_Framework_TestCase {

    private $object;

    public function setUp() {
        $this->object = new MaintenabilityFactor(new Calculator());
    }

    /**
     * @dataProvider provider
     */
    public function testCalculateMaintenability($expected, $maintenability) {

        $groupedResults = $this->getMockBuilder('\Hal\Component\Result\ResultCollection')->disableOriginalConstructor()->getMock();
        $collection = $this->getMockBuilder('\Hal\Component\Result\ResultCollection')->disableOriginalConstructor()->getMock();

        $bound = $this->getMockBuilder('\Hal\Component\Bounds\Result\ResultInterface')->disableOriginalConstructor()->getMock();
        $bound->expects($this->any())->method('getAverage')->will($this->returnValue($maintenability));

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