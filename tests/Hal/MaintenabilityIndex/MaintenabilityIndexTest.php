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
}