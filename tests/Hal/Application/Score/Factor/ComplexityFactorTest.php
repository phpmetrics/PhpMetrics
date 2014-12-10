<?php
namespace Test\Hal\Application\Score\Factor;
use Hal\Application\Score\Calculator;
use Hal\Application\Score\Factor\ComplexityFactor;


/**
 * @group score
 */
class ComplexityFactorTest extends \PHPUnit_Framework_TestCase {

    private $object;

    public function setUp() {
        $this->object = new ComplexityFactor(new Calculator());
    }

    /**
     * @dataProvider provider
     */
    public function testCalculateComplexity($expected, $maintenability) {

        $groupedResults = $this->getMockBuilder('\Hal\Component\Result\ResultCollection')->disableOriginalConstructor()->getMock();
        $collection = $this->getMockBuilder('\Hal\Component\Result\ResultCollection')->disableOriginalConstructor()->getMock();

        $bound = $this->getMockBuilder('\Hal\Component\Bounds\Result\ResultInterface')->disableOriginalConstructor()->getMock();
        $bound->expects($this->any())->method('getAverage')->will($this->returnValue($maintenability));

        $score = $this->object->calculate($collection, $groupedResults, $bound);
        $this->assertEquals($expected, $score);
    }

    public function provider() {
        return array(
            array( 8.33,  6)
            , array(33.33, 3)
            , array(50, 1)
        );
    }
}