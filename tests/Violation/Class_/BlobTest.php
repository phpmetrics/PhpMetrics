<?php

namespace Test\Hal\Violation\Class_;

use Hal\Metric\ClassMetric;
use Hal\Violation\Class_\Blob;
use Hal\Violation\Violations;

/**
 * @group violation
 */
class BlobTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @dataProvider provideExamples
     */
    public function testGlobIsFound($expected, $nbMethodsPublic, $lcom, $nbExternals)
    {
        $class = $this->getMockBuilder(ClassMetric::class)->disableOriginalConstructor()->getMock();

        $violations = new Violations();
        $class->method('get')->willReturnCallback(function ($param) use (
            $violations,
            $nbMethodsPublic,
            $lcom,
            $nbExternals
        ) {
            switch ($param) {
                case 'nbMethodsPublic':
                    return $nbMethodsPublic;
                case 'lcom':
                    return $lcom;
                case 'externals':
                    return array_pad([], $nbExternals, '');
                case 'violations':
                    return $violations;
            }
        });

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
