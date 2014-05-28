<?php
namespace Test\Hal\Metrics\Mood\Instability;
use Hal\Component\Result\ResultCollection;
use Hal\Metrics\Mood\Abstractness\Abstractness;
use Hal\Metrics\Mood\Abstractness\Result;

/**
 * @group mood
 * @group abstractness
 * @group metric
 */
class AbstractnessTest extends \PHPUnit_Framework_TestCase {


    /**
     * @dataProvider provideAbstractness
     */
    public function testICanKnowTheAbstractnessOfPackage($expected, $abstractClasses, $concreteClasses) {

        $instability = new Abstractness();
        $results = new ResultCollection();

        $oop = $this->getMockBuilder('\Hal\Component\OOP\Extractor\Result')->disableOriginalConstructor()->getMock();
        $oop->expects($this->any())->method('getConcreteClasses')->will($this->returnValue(array_pad(array(), $concreteClasses, null)));
        $oop->expects($this->any())->method('getAbstractClasses')->will($this->returnValue(array_pad(array(), $abstractClasses, null)));
        $resultSet = $this->getMockBuilder('\Hal\Component\Result\ResultSet')->disableOriginalConstructor()->getMock();
        $resultSet->expects($this->any())->method('getOOP')->will($this->returnValue($oop));
        $results->push($resultSet);

        $r = $instability->calculate($results);

        $this->assertEquals($expected, $r->getAbstractness());
    }

    public function provideAbstractness() {
        return array(
            array(1, 4, 0)
            , array(.5, 2, 2)
            , array(0, 0, 2)
        );
    }

    public function testAbstractnessResultCanBeConvertedToArray() {
        $result = new Result();
        $array = $result->asArray();
        $this->assertArrayHasKey('abstractness', $array);
    }

}