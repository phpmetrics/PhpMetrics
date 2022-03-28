<?php
declare(strict_types=1);

namespace Tests\Hal\Violation\Package;

use Generator;
use Hal\Metric\Metric;
use Hal\Metric\PackageMetric;
use Hal\Violation\Package\StableAbstractionsPrinciple;
use Hal\Violation\Violation;
use Hal\Violation\ViolationsHandlerInterface;
use Phake;
use Phake\IMock;
use PHPUnit\Framework\TestCase;
use function sqrt;

final class StableAbstractionsPrincipleTest extends TestCase
{
    public function testViolationLevel(): void
    {
        self::assertSame(Violation::WARNING, (new StableAbstractionsPrinciple())->getLevel());
    }

    public function testViolationName(): void
    {
        self::assertSame('Stable Abstractions Principle', (new StableAbstractionsPrinciple())->getName());
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
        Phake::when($packageMetric)->__call('getDistance', [])->thenReturn(0);
        yield 'Distance is 0' => [$packageMetric, $violationsHandler, false];

        $violationsHandler = Phake::mock(ViolationsHandlerInterface::class);
        $packageMetric = Phake::mock(PackageMetric::class);
        Phake::when($packageMetric)->__call('get', ['violations'])->thenReturn($violationsHandler);
        Phake::when($packageMetric)->__call('getDistance', [])->thenReturn(sqrt(2) / 4);
        yield 'Distance is positive and not too far away' => [$packageMetric, $violationsHandler, false];

        $violationsHandler = Phake::mock(ViolationsHandlerInterface::class);
        $packageMetric = Phake::mock(PackageMetric::class);
        Phake::when($packageMetric)->__call('get', ['violations'])->thenReturn($violationsHandler);
        Phake::when($packageMetric)->__call('getDistance', [])->thenReturn(-sqrt(2) / 4);
        yield 'Distance is negative and not too far away' => [$packageMetric, $violationsHandler, false];

        $violationsHandler = Phake::mock(ViolationsHandlerInterface::class);
        $packageMetric = Phake::mock(PackageMetric::class);
        Phake::when($packageMetric)->__call('get', ['violations'])->thenReturn($violationsHandler);
        Phake::when($packageMetric)->__call('getDistance', [])->thenReturn(sqrt(2.1) / 4);
        yield 'Distance is positive but too far away' => [$packageMetric, $violationsHandler, true];

        $violationsHandler = Phake::mock(ViolationsHandlerInterface::class);
        $packageMetric = Phake::mock(PackageMetric::class);
        Phake::when($packageMetric)->__call('get', ['violations'])->thenReturn($violationsHandler);
        Phake::when($packageMetric)->__call('getDistance', [])->thenReturn(-sqrt(2.1) / 4);
        yield 'Distance is negative but too far away' => [$packageMetric, $violationsHandler, true];

        $violationsHandler = Phake::mock(ViolationsHandlerInterface::class);
        $packageMetric = Phake::mock(PackageMetric::class);
        Phake::when($packageMetric)->__call('get', ['violations'])->thenReturn($violationsHandler);
        Phake::when($packageMetric)->__call('getDistance', [])->thenReturn(1);
        yield 'Maximum distance' => [$packageMetric, $violationsHandler, true];

        $violationsHandler = Phake::mock(ViolationsHandlerInterface::class);
        $packageMetric = Phake::mock(PackageMetric::class);
        Phake::when($packageMetric)->__call('get', ['violations'])->thenReturn($violationsHandler);
        Phake::when($packageMetric)->__call('getDistance', [])->thenReturn(-1);
        yield 'Minimum distance' => [$packageMetric, $violationsHandler, true];
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
        $violation = new StableAbstractionsPrinciple();
        $violation->apply($metric);

        if (false === $violate) {
            Phake::verifyNoInteraction($violationsHandler);
            return;
        }

        /** @var IMock&PackageMetric $metric */
        Phake::verify($metric)->__call('get', ['violations']);
        Phake::verify($violationsHandler)->__call('add', [$violation]);
        Phake::verifyNoOtherInteractions($violationsHandler);
        self::assertSame($this->getExpectedDescription($metric), $violation->getDescription());
    }

    /**
     * Returns the expected description of the current violation based on the values stored in the given metrics.
     *
     * @param PackageMetric $metric
     * @return string
     */
    private function getExpectedDescription(PackageMetric $metric): string
    {
        $violation = $metric->getDistance() > 0 ? 'unstable and abstract' : 'stable and concrete';
        return <<<EOT
Packages should be either abstract and stable or concrete and unstable.

This package is $violation.
EOT;
    }
}
