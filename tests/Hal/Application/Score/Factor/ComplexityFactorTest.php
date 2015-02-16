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
    public function testCalculateComplexity($expected, $maintainability) {

        $groupedResults = $this->getMockBuilder('\Hal\Component\Result\ResultCollection')->disableOriginalConstructor()->getMock();
        $collection = $this->getMockBuilder('\Hal\Component\Result\ResultCollection')->disableOriginalConstructor()->getMock();

        $bound = $this->getMockBuilder('\Hal\Component\Bounds\Result\ResultInterface')->disableOriginalConstructor()->getMock();
        $bound->expects($this->any())->method('getAverage')->will($this->returnValue($maintainability));

        $score = $this->object->calculate($collection, $groupedResults, $bound);
        $this->assertEquals($expected, $score);
    }

    public function provider() {
        return array(
            array( 28.57,  6)
            , array(71.43, 3)
            , array(100, 1)
        );
    }
}