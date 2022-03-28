<?php
declare(strict_types=1);

namespace Tests\Hal\Violation\Package;

use Generator;
use Hal\Metric\Metric;
use Hal\Metric\PackageMetric;
use Hal\Violation\Package\StableDependenciesPrinciple;
use Hal\Violation\Violation;
use Hal\Violation\ViolationsHandlerInterface;
use Phake;
use Phake\IMock;
use PHPUnit\Framework\TestCase;
use function array_filter;
use function array_keys;
use function array_map;
use function implode;
use function round;
use function sprintf;
use function substr;

final class StableDependenciesPrincipleTest extends TestCase
{
    public function testViolationLevel(): void
    {
        self::assertSame(Violation::WARNING, (new StableDependenciesPrinciple())->getLevel());
    }

    public function testViolationName(): void
    {
        self::assertSame('Stable Dependencies Principle', (new StableDependenciesPrinciple())->getName());
    }

    /**
     * @return Generator<string, array{0: IMock&Metric, 1: IMock&ViolationsHandlerInterface, 2: bool}>
     */
    public function provideMetricToCheckIfViolationApplies(): Generator
    {
        yield 'Invalid metric' => [Phake::mock(Metric::class), Phake::mock(ViolationsHandlerInterface::class), false];

        $violationsHandler = Phake::mock(ViolationsHandlerInterface::class);
        $packageMetric = Phake::mock(PackageMetric::class);
        Phake::when($packageMetric)->__call('get', ['violations'])->thenReturn($violationsHandler);
        Phake::when($packageMetric)->__call('getInstability', [])->thenReturn(32.45);
        Phake::when($packageMetric)->__call('getDependentInstabilities', [])->thenReturn([]);
        yield 'No dependencies' => [$packageMetric, $violationsHandler, false];

        $violationsHandler = Phake::mock(ViolationsHandlerInterface::class);
        $packageMetric = Phake::mock(PackageMetric::class);
        Phake::when($packageMetric)->__call('get', ['violations'])->thenReturn($violationsHandler);
        Phake::when($packageMetric)->__call('getInstability', [])->thenReturn(32.45);
        $dependentInstabilities = ['\\' => 32.44];
        Phake::when($packageMetric)->__call('getDependentInstabilities', [])->thenReturn($dependentInstabilities);
        yield 'Single dependency: more stable' => [$packageMetric, $violationsHandler, false];

        $violationsHandler = Phake::mock(ViolationsHandlerInterface::class);
        $packageMetric = Phake::mock(PackageMetric::class);
        Phake::when($packageMetric)->__call('get', ['violations'])->thenReturn($violationsHandler);
        Phake::when($packageMetric)->__call('getInstability', [])->thenReturn(32.45);
        $dependentInstabilities = ['\\' => 32.45];
        Phake::when($packageMetric)->__call('getDependentInstabilities', [])->thenReturn($dependentInstabilities);
        yield 'Single dependency: more unstable' => [$packageMetric, $violationsHandler, true];

        $violationsHandler = Phake::mock(ViolationsHandlerInterface::class);
        $packageMetric = Phake::mock(PackageMetric::class);
        Phake::when($packageMetric)->__call('get', ['violations'])->thenReturn($violationsHandler);
        Phake::when($packageMetric)->__call('getInstability', [])->thenReturn(32.45);
        $dependentInstabilities = ['\\' => 32.44, '\\A' => 30, '\\B' => 1];
        Phake::when($packageMetric)->__call('getDependentInstabilities', [])->thenReturn($dependentInstabilities);
        yield '3 dependencies: all more stable' => [$packageMetric, $violationsHandler, false];

        $violationsHandler = Phake::mock(ViolationsHandlerInterface::class);
        $packageMetric = Phake::mock(PackageMetric::class);
        Phake::when($packageMetric)->__call('get', ['violations'])->thenReturn($violationsHandler);
        Phake::when($packageMetric)->__call('getInstability', [])->thenReturn(32.45);
        $dependentInstabilities = ['\\' => 32.45, '\\A' => 40, '\\B' => 100];
        Phake::when($packageMetric)->__call('getDependentInstabilities', [])->thenReturn($dependentInstabilities);
        yield '3 dependencies: all more unstable' => [$packageMetric, $violationsHandler, true];

        $violationsHandler = Phake::mock(ViolationsHandlerInterface::class);
        $packageMetric = Phake::mock(PackageMetric::class);
        Phake::when($packageMetric)->__call('get', ['violations'])->thenReturn($violationsHandler);
        Phake::when($packageMetric)->__call('getInstability', [])->thenReturn(32.45);
        $dependentInstabilities = ['\\' => 32.44, '\\A' => 32.45, '\\B' => 32.46];
        Phake::when($packageMetric)->__call('getDependentInstabilities', [])->thenReturn($dependentInstabilities);
        yield '3 dependencies: stable, same, and unstable' => [$packageMetric, $violationsHandler, true];
    }

    /**
     * @dataProvider provideMetricToCheckIfViolationApplies
     * @param IMock&Metric $metric
     * @param ViolationsHandlerInterface&IMock $violationsHandler
     * @param bool $violate
     * @return void
     */
    //#[DataProvider('provideMetricToCheckIfViolationApplies')] TODO: PHPUnit 10
    public function testViolationApplies(
        IMock&Metric $metric,
        IMock&ViolationsHandlerInterface $violationsHandler,
        bool $violate
    ): void {
        $violation = new StableDependenciesPrinciple();
        $violation->apply($metric);

        if (false === $violate) {
            Phake::verifyNoInteraction($violationsHandler);
            return;
        }

        /** @var IMock&PackageMetric $metric */
        Phake::verify($metric)->__call('get', ['violations']);
        Phake::verify($violationsHandler)->__call('add', [$violation]);
        Phake::verifyNoOtherInteractions($violationsHandler);
        $instability = $metric->getInstability();
        $violatingInstabilities = array_filter(
            $metric->getDependentInstabilities(),
            static fn (float $otherInstability): bool => $otherInstability >= $instability
        );
        self::assertSame($this->getExpectedDescription($metric, $violatingInstabilities), $violation->getDescription());
    }

    /**
     * Returns the expected description of the current violation based on the values stored in the given metrics.
     *
     * @param PackageMetric $metric
     * @param array<string, float> $violatingInstabilities
     * @return string
     */
    private function getExpectedDescription(PackageMetric $metric, array $violatingInstabilities): string
    {
        $count = count($violatingInstabilities);
        $thisInstability = round($metric->getInstability(), 3);
        $packages = implode(
            "\n* ",
            array_map(static function (string $name, float $instability): string {
                $name = '\\' === $name ? 'global' : substr($name, 0, -1);
                return sprintf('%s (%f0.3)', $name, round($instability, 3));
            }, array_keys($violatingInstabilities), $violatingInstabilities)
        );
        return <<<EOT
Packages should depend in the direction of stability.

This package is more stable ($thisInstability) than $count package(s) that it depends on.
The packages that are more stable are

* $packages
EOT;
    }
}
