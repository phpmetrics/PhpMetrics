<?php

namespace Violation\Package;

use Hal\Metric\Metric;
use Hal\Metric\PackageMetric;
use Hal\Violation\Package\StableAbstractionsPrinciple;
use Hal\Violation\Violations;
use PHPUnit\Framework\Attributes\DataProvider;
use \PHPUnit\Framework\TestCase;

/**
 * @group violation
 */
class StableAbstractionsPrincipleTest extends TestCase
{
    public function testItIgnoresNonPackageMetrics(): void
    {
        $metric = $this->getMockBuilder(Metric::class)
            ->disableOriginalConstructor()
            ->getMock();

        $metric->expects($this->never())
            ->method('get')
            ->with('violations');

        $object = new StableAbstractionsPrinciple();

        $object->apply($metric);
    }

    /**
     * @dataProvider provideExamples
     */
    #[DataProvider('provideExamples')]
    public function testItAddsViolationsIfAPackageIsEitherStableAndConcreteOrInstableAndAbstract($abstractness, $instability, $expectedViolationCount): void
    {
        $metric = new PackageMetric('package');
        $metric->set('violations', new Violations());
        $metric->setNormalizedDistance($abstractness + $instability - 1);

        $object = new StableAbstractionsPrinciple();

        $object->apply($metric);

        $this->assertSame($expectedViolationCount, $metric->get('violations')->count());
    }

    public static function provideExamples()
    {
        return [
            'highly instable and highly concrete' => [1, 0, 0],
            'highly stable and highly abstract' => [0, 1, 0],
            'highly instable and highly abstract' => [1, 1, 1],
            'highly stable and highly concrete' => [0, 0, 1],
            'instable and concrete' => [0.76, 0.24, 0],
            'stable and abstract' => [0.24, 0.76, 0],
            'instable and abstract' => [0.76, 0.76, 1],
            'stable and concrete' => [0.24, 0.24, 1],
        ];
    }
}
