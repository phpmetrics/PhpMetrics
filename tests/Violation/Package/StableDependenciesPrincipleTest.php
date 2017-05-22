<?php

namespace Violation\Package;

use Hal\Metric\Metric;
use Hal\Metric\PackageMetric;
use Hal\Violation\Package\StableDependenciesPrinciple;
use Hal\Violation\Violations;
use PHPUnit_Framework_TestCase;

/**
 * @group violation
 */
class StableDependenciesPrincipleTest extends PHPUnit_Framework_TestCase
{
    public function testItIgnoresNonPackageMetrics()
    {
        $metric = $this->prophesize(Metric::class);

        $object = new StableDependenciesPrinciple();

        $object->apply($metric->reveal());
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
        $metric = $this->prophesize(PackageMetric::class);
        $metric->getInstability()->willReturn($packageInstability);
        $metric->getDependentInstabilities()->willReturn($dependentInstabilities);
        $metric->get('violations')->willReturn($violations);

        $object = new StableDependenciesPrinciple();

        $object->apply($metric->reveal());

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
