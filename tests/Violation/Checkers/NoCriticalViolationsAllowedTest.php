<?php
declare(strict_types=1);

namespace Tests\Hal\Violation\Checkers;

use Generator;
use Hal\Exception\ViolationsCheckerException;
use Hal\Metric\Metric;
use Hal\Metric\Metrics;
use Hal\Violation\Checkers\NoCriticalViolationsAllowed;
use Hal\Violation\Violation;
use Hal\Violation\ViolationsHandlerInterface;
use Phake;
use Phake\IMock;
use PHPUnit\Framework\TestCase;
use function array_map;

final class NoCriticalViolationsAllowedTest extends TestCase
{
    /**
     * @return Generator<string, array{0: IMock&Metrics, 1: null|ViolationsCheckerException}>
     */
    public function provideMetricsThatCouldHaveCriticalViolations(): Generator
    {
        $metrics = Phake::mock(Metrics::class);
        $metricList = [Phake::mock(Metric::class), Phake::mock(Metric::class), Phake::mock(Metric::class)];
        $violationsHandlersList = [
            Phake::mock(ViolationsHandlerInterface::class),
            Phake::mock(ViolationsHandlerInterface::class),
            Phake::mock(ViolationsHandlerInterface::class),
        ];
        foreach ($violationsHandlersList as $violationsHandler) {
            Phake::when($violationsHandler)->__call('getAll', [])->thenReturn([]);
        }
        array_map(
            static function (IMock $metric, IMock $violationsHandler): void {
                Phake::when($metric)->__call('get', ['violations'])->thenReturn($violationsHandler);
            },
            $metricList,
            $violationsHandlersList
        );
        Phake::when($metrics)->__call('all', [])->thenReturn($metricList);
        yield 'Metrics have no violation at all' => [$metrics, null];

        $metrics = Phake::mock(Metrics::class);
        $metricList = [Phake::mock(Metric::class), Phake::mock(Metric::class), Phake::mock(Metric::class)];
        $violationsHandlersList = [
            Phake::mock(ViolationsHandlerInterface::class),
            Phake::mock(ViolationsHandlerInterface::class),
            Phake::mock(ViolationsHandlerInterface::class),
        ];
        $violations = [Phake::mock(Violation::class), Phake::mock(Violation::class), Phake::mock(Violation::class)];
        Phake::when($violationsHandlersList[0])->__call('getAll', [])->thenReturn([]);
        Phake::when($violationsHandlersList[1])->__call('getAll', [])->thenReturn([]);
        Phake::when($violationsHandlersList[2])->__call('getAll', [])->thenReturn($violations);
        Phake::when($violations[0])->__call('getLevel', [])->thenReturn(Violation::INFO);
        Phake::when($violations[1])->__call('getLevel', [])->thenReturn(Violation::WARNING);
        Phake::when($violations[2])->__call('getLevel', [])->thenReturn(Violation::ERROR);
        array_map(
            static function (IMock $metric, IMock $violationsHandler): void {
                Phake::when($metric)->__call('get', ['violations'])->thenReturn($violationsHandler);
            },
            $metricList,
            $violationsHandlersList
        );
        Phake::when($metrics)->__call('all', [])->thenReturn($metricList);
        yield 'Metrics have no critical violation' => [$metrics, null];

        $metrics = Phake::mock(Metrics::class);
        $metricList = [Phake::mock(Metric::class), Phake::mock(Metric::class), Phake::mock(Metric::class)];
        $violationsHandlersList = [
            Phake::mock(ViolationsHandlerInterface::class),
            Phake::mock(ViolationsHandlerInterface::class),
            Phake::mock(ViolationsHandlerInterface::class),
        ];
        $violations = [Phake::mock(Violation::class), Phake::mock(Violation::class), Phake::mock(Violation::class)];
        Phake::when($violationsHandlersList[0])->__call('getAll', [])->thenReturn([]);
        Phake::when($violationsHandlersList[1])->__call('getAll', [])->thenReturn($violations);
        Phake::when($violationsHandlersList[2])->__call('getAll', [])->thenReturn([]);
        Phake::when($violations[0])->__call('getLevel', [])->thenReturn(Violation::CRITICAL);
        Phake::when($violations[1])->__call('getLevel', [])->thenReturn(Violation::WARNING);
        Phake::when($violations[2])->__call('getLevel', [])->thenReturn(Violation::ERROR);
        array_map(
            static function (IMock $metric, IMock $violationsHandler): void {
                Phake::when($metric)->__call('get', ['violations'])->thenReturn($violationsHandler);
            },
            $metricList,
            $violationsHandlersList
        );
        Phake::when($metrics)->__call('all', [])->thenReturn($metricList);
        $expectedException = ViolationsCheckerException::tooManyCriticalViolations(1, 0);
        yield 'Metrics have only 1 critical violation' => [$metrics, $expectedException];

        $metrics = Phake::mock(Metrics::class);
        $metricList = [Phake::mock(Metric::class), Phake::mock(Metric::class), Phake::mock(Metric::class)];
        $violationsHandlersList = [
            Phake::mock(ViolationsHandlerInterface::class),
            Phake::mock(ViolationsHandlerInterface::class),
            Phake::mock(ViolationsHandlerInterface::class),
        ];
        $violations = [Phake::mock(Violation::class), Phake::mock(Violation::class), Phake::mock(Violation::class)];
        Phake::when($violationsHandlersList[0])->__call('getAll', [])->thenReturn($violations);
        Phake::when($violationsHandlersList[1])->__call('getAll', [])->thenReturn([]);
        Phake::when($violationsHandlersList[2])->__call('getAll', [])->thenReturn($violations);
        Phake::when($violations[0])->__call('getLevel', [])->thenReturn(Violation::CRITICAL);
        Phake::when($violations[1])->__call('getLevel', [])->thenReturn(Violation::CRITICAL);
        Phake::when($violations[2])->__call('getLevel', [])->thenReturn(Violation::CRITICAL);
        array_map(
            static function (IMock $metric, IMock $violationsHandler): void {
                Phake::when($metric)->__call('get', ['violations'])->thenReturn($violationsHandler);
            },
            $metricList,
            $violationsHandlersList
        );
        Phake::when($metrics)->__call('all', [])->thenReturn($metricList);
        $expectedException = ViolationsCheckerException::tooManyCriticalViolations(6, 0);
        yield 'Metrics have several critical violations' => [$metrics, $expectedException];
    }

    /**
     * @dataProvider provideMetricsThatCouldHaveCriticalViolations
     * @param Metrics&IMock $metrics
     * @param ViolationsCheckerException|null $expectedException
     * @return void
     */
    //#[DataProvider('provideMetricsThatCouldHaveCriticalViolations')] TODO: PHPUnit 10.
    public function testICanCheckTheViolationsOnMetrics(
        IMock&Metrics $metrics,
        null|ViolationsCheckerException $expectedException
    ): void {
        $violationChecker = new NoCriticalViolationsAllowed($metrics);

        if (null !== $expectedException) {
            $this->expectExceptionObject($expectedException);
        }

        $violationChecker->check();
        Phake::verify($metrics)->__call('all', []);
        Phake::verifyNoOtherInteractions($metrics);
    }
}
