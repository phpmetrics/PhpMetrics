<?php
declare(strict_types=1);

namespace Tests\Hal\Violation\Class_;

use Generator;
use Hal\Metric\ClassMetric;
use Hal\Metric\Metric;
use Hal\Violation\Class_\ProbablyBugged;
use Hal\Violation\Violation;
use Hal\Violation\ViolationsHandlerInterface;
use Phake;
use Phake\IMock;
use PHPUnit\Framework\TestCase;

final class ProbablyBuggedTest extends TestCase
{
    public function testViolationLevel(): void
    {
        self::assertSame(Violation::WARNING, (new ProbablyBugged())->getLevel());
    }

    public function testViolationName(): void
    {
        self::assertSame('Probably bugged', (new ProbablyBugged())->getName());
    }

    /**
     * @return Generator<string, array{0: IMock&Metric, 1: IMock&ViolationsHandlerInterface, 2: bool}>
     */
    public function provideMetricToCheckIfViolationApplies(): Generator
    {
        yield 'Invalid metric' => [Phake::mock(Metric::class), Phake::mock(ViolationsHandlerInterface::class), false];

        $violationsHandler = Phake::mock(ViolationsHandlerInterface::class);
        $classMetric = Phake::mock(ClassMetric::class);
        Phake::when($classMetric)->__call('get', ['violations'])->thenReturn($violationsHandler);
        Phake::when($classMetric)->__call('get', ['numberOfUnitTests'])->thenReturn(3);
        Phake::when($classMetric)->__call('get', ['bugs'])->thenReturn(0.3499);
        yield 'Bugs probability too low' => [$classMetric, $violationsHandler, false];

        $violationsHandler = Phake::mock(ViolationsHandlerInterface::class);
        $classMetric = Phake::mock(ClassMetric::class);
        Phake::when($classMetric)->__call('get', ['violations'])->thenReturn($violationsHandler);
        Phake::when($classMetric)->__call('get', ['numberOfUnitTests'])->thenReturn(3);
        Phake::when($classMetric)->__call('get', ['bugs'])->thenReturn(0.35);
        yield 'Violations (edge mode)' => [$classMetric, $violationsHandler, true];

        $violationsHandler = Phake::mock(ViolationsHandlerInterface::class);
        $classMetric = Phake::mock(ClassMetric::class);
        Phake::when($classMetric)->__call('get', ['violations'])->thenReturn($violationsHandler);
        Phake::when($classMetric)->__call('get', ['numberOfUnitTests'])->thenReturn(3);
        Phake::when($classMetric)->__call('get', ['bugs'])->thenReturn(896.34);
        yield 'Violations (overkill)' => [$classMetric, $violationsHandler, true];
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
        $violation = new ProbablyBugged();
        $violation->apply($metric);

        if (false === $violate) {
            Phake::verifyNoInteraction($violationsHandler);
            return;
        }

        Phake::verify($metric)->__call('get', ['violations']);
        Phake::verify($violationsHandler)->__call('add', [$violation]);
        Phake::verifyNoOtherInteractions($violationsHandler);
        self::assertSame($this->getExpectedDescription($metric), $violation->getDescription());
    }

    /**
     * Returns the expected description of the current violation based on the values stored in the given metrics.
     *
     * @param Metric $metric
     * @return string
     */
    private function getExpectedDescription(Metric $metric): string
    {
        return <<<EOT
This component contains in theory {$metric->get('bugs')} bugs.

* Calculation is based on number of operators, operands, cyclomatic complexity
* See more details at https://en.wikipedia.org/wiki/Halstead_complexity_measures
* {$metric->get('numberOfUnitTests')} testsuites has dependency to this class.

Maybe you should check your unit tests for this class.
EOT;
    }
}
