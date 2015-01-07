<?php
namespace Test\Hal\Metrics\Complexity\Text\Halstead;

use Hal\Metrics\Design\Component\MaintenabilityIndex\MaintenabilityIndex;
use Hal\Metrics\Design\Component\MaintenabilityIndex\Result;

/**
 * @group maintainability
 * @group metric
 */
class MaintenabilityIndexTest extends \PHPUnit_Framework_TestCase {


    public function testMaintenabilityIndexServiceReturnsResult() {

        $rLoc = $this->getMock('\Hal\Metrics\Complexity\Text\Length\Result');
        $rLoc->expects($this->any())->method('getLOC')->will($this->returnValue(5));
        $rHalstead = $this->getMock('\Hal\Metrics\Complexity\Text\Halstead\Result');
        $rMcCabe = $this->getMock('\Hal\Metrics\Complexity\Component\McCabe\Result');

        $object = new MaintenabilityIndex();
        $result = $object->calculate($rHalstead, $rLoc, $rMcCabe);

        $this->assertInstanceOf("\Hal\Metrics\Design\Component\MaintenabilityIndex\Result", $result);
    }

    public function testMaintenabilityIndexResultCanBeConvertedToArray() {

        $result = new Result();
        $array = $result->asArray();

        $this->assertArrayHasKey('maintenabilityIndex', $array);
    }

    public function testMaintenabilityIndexResultContainsCommentWeight() {
        $result = new Result();

        $this->assertArrayHasKey('commentWeight', $result->asArray());
    }

    /**
     * @dataProvider provideValues
     */
    public function testMaintenabilityIndexWithoutCommentIsWellCalculated($cc, $lloc, $cloc, $volume, $MIwoC) {

        $rLoc = $this->getMock('\Hal\Metrics\Complexity\Text\Length\Result');
        $rLoc->expects($this->once())->method('getLogicalLoc')->will($this->returnValue($lloc));
        $rHalstead = $this->getMock('\Hal\Metrics\Complexity\Text\Halstead\Result');
        $rHalstead->expects($this->once())->method('getVolume')->will($this->returnValue($volume));
        $rMcCabe = $this->getMock('\Hal\Metrics\Complexity\Component\McCabe\Result');
        $rMcCabe->expects($this->once())->method('getCyclomaticComplexityNumber')->will($this->returnValue($cc));


        $object = new MaintenabilityIndex();
        $result = $object->calculate($rHalstead, $rLoc, $rMcCabe);

        $this->assertEquals($MIwoC, $result->getMaintenabilityIndexWithoutComment());


    }

    public function provideValues() {
        return array(
            //    CC    LLOC    CLOC        Volume  MIwoC
            array(5     , 50    , 20       , 10    , 55.26 )
        );
    }

}