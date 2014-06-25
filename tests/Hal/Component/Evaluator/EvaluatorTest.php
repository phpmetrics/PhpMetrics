<?php
namespace Test\Hal\Component\Evaluation;
use Hal\Component\Bounds\Bounds;
use Hal\Component\Evaluation\Evaluator;


/**
 * @group bre
 */
class EvaluatorTest extends \PHPUnit_Framework_TestCase {

    public function testICanEvaluateRule() {

        $bound = new Bounds();
        $collection = $this->getMockBuilder('\Hal\Component\Result\ResultCollection')->disableOriginalConstructor()->getMock();
        $aggregatedCollection = $this->getMockBuilder('\Hal\Component\Result\ResultCollection')->disableOriginalConstructor()->getMock();

        $collection->expects($this->any())->method('asArray')->will($this->returnValue(array(
            'average' => array('loc' => 15, 'logicalLoc' => 12)
            , 'sum' => array('loc' => 150, 'logicalLoc' => 120)
        )));
        $aggregatedCollection->expects($this->any())->method('getIterator')->will($this->returnValue(new \ArrayIterator(array())));


        $evaluator = new Evaluator($collection, $aggregatedCollection, $bound);

        $result = $evaluator->evaluate('average.loc > 10 and sum.loc < 200');
        $this->assertTrue($result->isValid());

        $result = $evaluator->evaluate('average.loc > 100');
        $this->assertFalse($result->isValid());
    }

    public function testICanEvaluateRuleConcerningPackage() {

        $bound = new Bounds();
        $collection = $this->getMockBuilder('\Hal\Component\Result\ResultCollection')->disableOriginalConstructor()->getMock();
        $aggregatedCollection = $this->getMockBuilder('\Hal\Component\Result\ResultCollection')->disableOriginalConstructor()->getMock();
        $collection->expects($this->any())->method('asArray')->will($this->returnValue(array(
            'average' => array('loc' => 15, 'logicalLoc' => 12)
            , 'sum' => array('loc' => 150, 'logicalLoc' => 120)
        )));


        $packageResult = $this->getMockBuilder('\Hal\Component\Result\ResultAggregate')->disableOriginalConstructor()->getMock();
        $packageResult->expects($this->any())->method('asArray')->will($this->returnValue(array(
            'average' => array('loc' => 15, 'logicalLoc' => 12)
            , 'sum' => array('loc' => 150, 'logicalLoc' => 120)
        )));
        $packageResult->expects($this->any())->method('getName')->will($this->returnValue('Abc/Def'));
        $aggregatedCollection->expects($this->any())->method('getIterator')->will($this->returnValue(new \ArrayIterator(array($packageResult))));



        $evaluator = new Evaluator($collection, $aggregatedCollection, $bound);

        $result = $evaluator->evaluate('Abc/Def.sum.loc < 200');
        $this->assertTrue($result->isValid());

        $result = $evaluator->evaluate('Abc/Def.sum.loc > 200');
        $this->assertFalse($result->isValid());
    }
}