<?php
namespace Test\Hal\Component\Result;

use Hal\Component\Result\ResultAggregate;

/**
 * @group result
 */
class ResultAggregateTest extends \PHPUnit_Framework_TestCase {

   public function testResultAggregateCanBeExportedToArray() {


       $abstractness = $this->getMock('\Hal\Metrics\Mood\Abstractness\Result');
       $abstractness->expects($this->once())->method('asArray')->will($this->returnValue(array('abstractness' => 1)));

       $instability = $this->getMock('\Hal\Metrics\Mood\Instability\Result');
       $instability->expects($this->once())->method('asArray')->will($this->returnValue(array('instability' => .3)));

       $resultAggregate = new ResultAggregate('my');
       $resultAggregate->setAbstractness($abstractness)->setInstability($instability);

       $expected = array(
           'name' => 'my'
           , 'abstractness' => 1
           , 'instability' => .3
           , 'childs' => array()
           , 'depth' => 1
       );

       $this->assertEquals($expected, $resultAggregate->asArray());

   }
}