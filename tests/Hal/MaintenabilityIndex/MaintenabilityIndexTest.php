<?php
namespace Test\Hal\Halstead;

use Hal\MaintenabilityIndex\MaintenabilityIndex;
use Hal\MaintenabilityIndex\Result;

/**
 * @group maintenability
 * @group metric
 */
class MaintenabilityIndexTest extends \PHPUnit_Framework_TestCase {


    public function testMaintenabilityIndexServiceReturnsResult() {

        $rLoc = $this->getMock('\Hal\Loc\Result');
        $rLoc->expects($this->any())->method('getLOC')->will($this->returnValue(5));
        $rHalstead = $this->getMock('\Hal\Halstead\Result');

        $object = new MaintenabilityIndex();
        $result = $object->calculate($rHalstead, $rLoc);

        $this->assertInstanceOf("\Hal\MaintenabilityIndex\Result", $result);
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

        $rLoc = $this->getMock('\Hal\Loc\Result');
        $rLoc->expects($this->once())->method('getComplexityCyclomatic')->will($this->returnValue($cc));
        $rLoc->expects($this->once())->method('getLogicalLoc')->will($this->returnValue($lloc));
        $rHalstead = $this->getMock('\Hal\Halstead\Result');
        $rHalstead->expects($this->once())->method('getVolume')->will($this->returnValue($volume));

        $object = new MaintenabilityIndex();
        $result = $object->calculate($rHalstead, $rLoc);

        $this->assertEquals($MIwoC, $result->getMaintenabilityIndexWithoutComment());


    }

    public function provideValues() {
        return array(
            //    CC    LLOC    CLOC        Volume  MIwoC
            array(5     , 50    , 20       , 10    , 55.26 )
        );
    }

}