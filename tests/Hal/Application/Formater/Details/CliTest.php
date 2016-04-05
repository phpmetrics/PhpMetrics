<?php
namespace Test\Hal\Application\Formater\Summary;

use Hal\Component\Bounds\Bounds;
use Hal\Component\Bounds\Result\BoundsResult;
use Hal\Application\Formater\Details\Cli;
use Hal\Component\Result\ResultCollection;
use Symfony\Component\Console\Output\ConsoleOutput;


/**
 * @group formater
 */
class CliTest extends \PHPUnit_Framework_TestCase {

    public function testFormaterReturnsHtml() {

        $rs1 = $this->getMockBuilder('\Hal\Component\Result\ResultSet')->disableOriginalConstructor()->getMock();
        $rs1->expects($this->any())->method('asArray')->will($this->returnValue(array('volume' => 5)));
        $rs2 = $this->getMockBuilder('\Hal\Component\Result\ResultSet')->disableOriginalConstructor()->getMock();
        $rs2->expects($this->any())->method('asArray')->will($this->returnValue(array('volume' => 15)));

        $collection = $this->getMockBuilder('\Hal\Component\Result\ResultCollection') ->disableOriginalConstructor() ->getMock();
        $collection->expects($this->any()) ->method('getIterator') ->will($this->returnValue(new \ArrayIterator(array($rs1, $rs2))));
        $collection->expects($this->any()) ->method('asArray') ->will($this->returnValue(array(
                array('volume' => 5)
                , array('volume' => 10)
            )));
        $collection->expects($this->any()) ->method('getFilename') ->will($this->returnValue('abc'));

        $validator = $this->getMockBuilder('\Hal\Application\Rule\Validator')->disableOriginalConstructor()->getMock();

        $bounds = new Bounds();
        $formater = new Cli($validator, $bounds, $this->getMockBuilder('\Hal\Application\Extension\ExtensionService')->disableOriginalConstructor()->getMock());
        $groupedResults = new ResultCollection();
        $output = $formater->terminate($collection, $groupedResults);
        $this->assertRegExp('/Maintainability/', $output);
    }

    public function testFormaterHasName() {
        $validator = $this->getMockBuilder('\Hal\Application\Rule\Validator')->disableOriginalConstructor()->getMock();
        $bounds = new Bounds();
        $formater = new Cli($validator, $bounds, $this->getMockBuilder('\Hal\Application\Extension\ExtensionService')->disableOriginalConstructor()->getMock());
        $this->assertNotNull($formater->getName());
    }

    /**
     * @dataProvider validSecondsToTimeString
     */
    public function testFormaterReturnsCorrectDurationForUnderstanding($duration, $expectedString) {
        $collection = $this->getMockBuilder('\Hal\Component\Result\ResultCollection')
            ->disableOriginalConstructor()
            ->getMock();
        $collection->expects($this->any())
            ->method('asArray')
            ->will(
                $this->returnValue(
                    [
                        ['time' => $duration],
                    ]
                )
            );
        $validator = $this->getMockBuilder('\Hal\Application\Rule\Validator')->disableOriginalConstructor()->getMock();

        $bounds = new Bounds();
        $formater = new Cli($validator, $bounds, $this->getMockBuilder('\Hal\Application\Extension\ExtensionService')->disableOriginalConstructor()->getMock());
        $groupedResults = new ResultCollection();
        $output = $formater->terminate($collection, $groupedResults);
        $this->assertContains($expectedString, $output);
    }

    public function validSecondsToTimeString() {
        return [
            // zero
            [-1, '0 hour(s), 0 minute(s) and 1 second(s)'],
            // zero
            [0, '0 hour(s), 0 minute(s) and 0 second(s)'],
            // just seconds
            [25, '0 hour(s), 0 minute(s) and 25 second(s)'],
            // more than a minute
            [80, '0 hour(s), 1 minute(s) and 20 second(s)'],
            // less than an hour
            [3482, '0 hour(s), 58 minute(s) and 2 second(s)'],
            // more than an hour, less than a day
            [14532, '4 hour(s), 2 minute(s) and 12 second(s)'],
            // more than a day
            [132437, '36 hour(s), 47 minute(s) and 17 second(s)'],
        ];
    }
}