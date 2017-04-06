<?php
namespace Test\Hal\Violation\Class_;

use Hal\Violation\Class_\Blob;
use Hal\Violation\Violations;

/**
 * @group violation
 */
class BlobTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @dataProvider provideExamples
     */
    public function testGlobIsFound($expected, $nbMethodsPublic, $lcom, $nbExternals)
    {
        $prophet = $this->prophesize('Hal\Metric\ClassMetric');
        $prophet->get('nbMethodsPublic')->willReturn($nbMethodsPublic);
        $prophet->get('lcom')->willReturn($lcom);
        $prophet->get('externals')->willReturn(array_pad([], $nbExternals, ''));
        $prophet->get('violations')->willReturn(new Violations());
        $class = $prophet->reveal();

        $object = new Blob();
        $object->apply($class);
        $this->assertEquals($expected, $class->get('violations')->count());
    }

    public function provideExamples()
    {
        return [
            [1, 9, 3, 10],
            [0, 9, 3, 5],
            [0, 9, 1.4, 10],
            [0, 3, 3, 10],
        ];
    }
}
