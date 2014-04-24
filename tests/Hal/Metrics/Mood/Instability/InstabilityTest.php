<?php
namespace Test\Hal\Metrics\Mood\Instability;
use Hal\Component\Result\ResultCollection;
use Hal\Metrics\Mood\Instability\Instability;
use Hal\Metrics\Mood\Instability\Result;

/**
 * @group mood
 * @group instability
 * @group metric
 */
class InstabilityTest extends \PHPUnit_Framework_TestCase {


    public function testICanKnowTheInstabilityOfPackage() {

        $results = new ResultCollection();

        $coupling = $this->getMockBuilder('\Hal\Metrics\Complexity\Structural\HenryAndKafura\Result')->disableOriginalConstructor()->getMock();
        $coupling->expects($this->any())->method('getAfferentCoupling')->will($this->returnValue(1.5));
        $coupling->expects($this->any())->method('getEfferentCoupling')->will($this->returnValue(0.8));
        $resultSet = $this->getMockBuilder('\Hal\Component\Result\ResultSet')->disableOriginalConstructor()->getMock();
        $resultSet->expects($this->any())->method('getCoupling')->will($this->returnValue($coupling));
        $results->push($resultSet);

        $instability = new Instability();
        $r = $instability->calculate($results);

        $this->assertEquals(0.35, $r->getInstability());
    }

    public function testMaintenabilityIndexResultCanBeConvertedToArray() {
        $result = new Result();
        $array = $result->asArray();
        $this->assertArrayHasKey('instability', $array);
    }

}