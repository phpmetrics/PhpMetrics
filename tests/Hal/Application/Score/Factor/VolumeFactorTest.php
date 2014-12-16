<?php
namespace Test\Hal\Application\Score\Factor;
use Hal\Application\Score\Calculator;
use Hal\Application\Score\Factor\VolumeFactor;


/**
 * @group score
 */
class VolumeFactorTest extends \PHPUnit_Framework_TestCase {

    private $object;

    public function setUp() {
        $this->object = new VolumeFactor(new Calculator());
    }

    /**
     * @dataProvider provider
     */
    public function testCalculateVolume($expected, $loc, $lloc, $vocabulary) {

        $groupedResults = $this->getMockBuilder('\Hal\Component\Result\ResultCollection')->disableOriginalConstructor()->getMock();
        $collection = $this->getMockBuilder('\Hal\Component\Result\ResultCollection')->disableOriginalConstructor()->getMock();

        $bound = $this->getMockBuilder('\Hal\Component\Bounds\Result\ResultInterface')->disableOriginalConstructor()->getMock();
        $map = array(
            array('loc', $loc)
            , array('logicalLoc', $lloc)
            , array('vocabulary', $vocabulary)
        );
        $bound->method('getAverage') ->will($this->returnValueMap($map));

        $score = $this->object->calculate($collection, $groupedResults, $bound);
        $this->assertEquals($expected, $score);
    }

    public function provider() {
        return array(
            array( 19.1     ,  103  , 30    , 59)
            , array(71.73   , 40    , 15    , 45)
            , array(100     , 65    , 9     , 27)
        );
    }
}