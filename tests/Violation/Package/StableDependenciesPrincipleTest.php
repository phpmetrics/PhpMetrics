<?php

namespace Violation\Package;

use Hal\Metric\Metric;
use Hal\Metric\PackageMetric;
use Hal\Violation\Package\StableDependenciesPrinciple;
use Hal\Violation\Violations;
use \PHPUnit\Framework\TestCase;

/**
 * @group violation
 */
class StableDependenciesPrincipleTest extends TestCase
{
    public function testItIgnoresNonPackageMetrics()
    {
        $metric = $this->getMockBuilder(Metric::class)->getMock();
        $metric->expects($this->never())->method('get');
        $object = new StableDependenciesPrinciple();

        $object->apply($metric);
    }

    /**
     * @dataProvider provideExamples
     * @param float $packageInstability
     * @param float[] $dependentInstabilities
     * @param int $expectedViolationCount
     */
    public function testItAddsViolationsIfOneDependentPackageIsMoreUnstableOrAsUnstableAsThePackageItself(
        $packageInstability,
        array $dependentInstabilities,
        $expectedViolationCount
    ) {
        $violations = new Violations();
        $metric = $this->getMockBuilder(PackageMetric::class)->disableOriginalConstructor()->getMock();
        $metric->method('getInstability')->willReturn($packageInstability);
        $metric->method('getDependentInstabilities')->willReturn($dependentInstabilities);
        $metric->method('get')->willReturn($violations);

        $object = new StableDependenciesPrinciple();

        $object->apply($metric);

        $this->assertSame($expectedViolationCount, $violations->count());
    }

    public static function provideExamples()
    {
        return [
            'no dependents' => [2.2, [], 0],
            'dependents with a lower instability' => [2.2, [2.0], 0],
            'dependents with the same instability' => [2.2, [2.2], 1],
            'dependents with a bigger instability' => [2.2, [2.3], 1],
            'multiple dependents with a bigger instability' => [2.5, [2.2, 3.0, 2.5, 4.5], 1],
        ];
    }
}
