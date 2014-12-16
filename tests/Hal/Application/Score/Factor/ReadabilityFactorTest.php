<?php
namespace Test\Hal\Application\Score\Factor;
use Hal\Application\Score\Calculator;
use Hal\Application\Score\Factor\ReadabilityFactor;


/**
 * @group score
 */
class ReadabilityFactorTest extends \PHPUnit_Framework_TestCase {

    private $object;

    public function setUp() {
        $this->object = new ReadabilityFactor(new Calculator());
    }

    /**
     * @dataProvider provider
     */
    public function testCalculateReadability($expected, $commentWeight, $difficulty) {

        $groupedResults = $this->getMockBuilder('\Hal\Component\Result\ResultCollection')->disableOriginalConstructor()->getMock();
        $collection = $this->getMockBuilder('\Hal\Component\Result\ResultCollection')->disableOriginalConstructor()->getMock();

        $bound = $this->getMockBuilder('\Hal\Component\Bounds\Result\ResultInterface')->disableOriginalConstructor()->getMock();
        $map = array(
            array('difficulty', $difficulty),
            array('commentWeight', $commentWeight)
        );
        $bound->method('getAverage') ->will($this->returnValueMap($map));

        $score = $this->object->calculate($collection, $groupedResults, $bound);
        $this->assertEquals($expected, $score);
    }

    public function provider() {
        return array(
            array( 15,  35, 18)
            , array(52.3, 40, 15)
            , array(100, 42, 5.8)
        );
    }
}