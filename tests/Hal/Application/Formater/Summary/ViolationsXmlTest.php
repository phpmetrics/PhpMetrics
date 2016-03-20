<?php
namespace Test\Hal\Application\Formater\Summary;

use Hal\Component\Aggregator\DirectoryAggregator;
use Hal\Component\Bounds\Bounds;
use Hal\Application\Formater\Violations\Xml;
use Hal\Component\Result\ResultAggregate;
use Hal\Component\Result\ResultCollection;
use Symfony\Component\Console\Output\ConsoleOutput;


/**
 * @group formater
 */
class ViolationsXmlTest extends \PHPUnit_Framework_TestCase {

    public function testFormaterReturnsXml() {

        // all results
        $rs1 = $this->getMockBuilder('\Hal\Component\Result\ResultSet')->disableOriginalConstructor()->getMock();
        $rs1->expects($this->any())->method('asArray')->will($this->returnValue(array('loc' => 5)));
        $rs1->expects($this->any())->method('getFilename')->will($this->returnValue('path2/file1.php'));
        $rs2 = $this->getMockBuilder('\Hal\Component\Result\ResultSet')->disableOriginalConstructor()->getMock();
        $rs2->expects($this->any())->method('asArray')->will($this->returnValue(array('loc' => 15)));
        $rs2->expects($this->any())->method('getFilename')->will($this->returnValue('path1/file1.php'));

        $collection = $this->getMockBuilder('\Hal\Component\Result\ResultCollection') ->disableOriginalConstructor() ->getMock();
        $collection->expects($this->any()) ->method('getIterator') ->will($this->returnValue(new \ArrayIterator(array($rs1, $rs2))));
        $collection->expects($this->any()) ->method('getFilename') ->will($this->returnValue('abc'));
        $collection->expects($this->any()) ->method('asArray') ->will($this->returnValue(array(
            array('loc' => 5)
            , array('loc' => 15)
        )));
        $bounds = new Bounds();

        // grouped results
        $groupedResults = new ResultCollection();
        $result = $this->getMockBuilder('\Hal\Component\Result\ResultAggregate')->disableOriginalConstructor()->getMock();
        $result->expects($this->any())->method('getName')->will($this->returnValue('path1'));
        $result->expects($this->any())->method('getBounds')->will($this->returnValue($this->getMock('\Hal\Component\Bounds\Result\ResultInterface')));
        $groupedResults->push($result);
        $result = $this->getMockBuilder('\Hal\Component\Result\ResultAggregate')->disableOriginalConstructor()->getMock();
        $result->expects($this->any())->method('getName')->will($this->returnValue('path2'));
        $result->expects($this->any())->method('getBounds')->will($this->returnValue($this->getMock('\Hal\Component\Bounds\Result\ResultInterface')));
        $groupedResults->push($result);


        // formater
        $ruleSet = $this->getMock('\Hal\Application\Rule\RuleSet');
        $ruleSet->expects($this->any())->method('getRule')->will($this->returnValue(array(3, 2, 1.5)));
        $validator = $this->getMockBuilder('\Hal\Application\Rule\Validator')->disableOriginalConstructor()->getMock();
        $validator->expects($this->any())->method('getRuleSet')->willReturn($ruleSet);
        
        $formater = new Xml($validator, $bounds, $this->getMockBuilder('\Hal\Application\Extension\ExtensionService')->disableOriginalConstructor()->getMock());
        $output = $formater->terminate($collection, $groupedResults);


        $xml = new \SimpleXMLElement($output);
        $p = $xml->xpath('//pmd/file/violation');
        $this->assertCount(2, $p);
        $this->assertEquals('loc', $p[0]['rule']);
        $this->assertEquals('5', $p[0]['endline']);
        $this->assertEquals('loc', $p[1]['rule']);
        $this->assertEquals('15', $p[1]['endline']);
    }

    public function testFormaterHasName() {
        $validator = $this->getMockBuilder('\Hal\Application\Rule\Validator')->disableOriginalConstructor()->getMock();
        $bounds = new Bounds();
        $formater = new Xml($validator, $bounds, $this->getMockBuilder('\Hal\Application\Extension\ExtensionService')->disableOriginalConstructor()->getMock());
        $this->assertNotNull($formater->getName());
    }
}