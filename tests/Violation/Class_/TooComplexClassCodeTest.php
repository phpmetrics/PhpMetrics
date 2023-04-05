<?php
declare(strict_types=1);

namespace Tests\Hal\Violation\Class_;

use Generator;
use Hal\Metric\ClassMetric;
use Hal\Metric\Metric;
use Hal\Violation\Class_\TooComplexClassCode;
use Hal\Violation\Violation;
use Hal\Violation\ViolationsHandlerInterface;
use Phake;
use Phake\IMock;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

final class TooComplexClassCodeTest extends TestCase
{
    public function testViolationLevel(): void
    {
        self::assertSame(Violation::ERROR, (new TooComplexClassCode())->getLevel());
    }

    public function testViolationName(): void
    {
        self::assertSame('Too complex class code', (new TooComplexClassCode())->getName());
    }

    /**
     * @return Generator<string, array{IMock&Metric, IMock&ViolationsHandlerInterface, bool}>
     */
    public static function provideMetricToCheckIfViolationApplies(): Generator
    {
        yield 'Invalid metric' => [Phake::mock(Metric::class), Phake::mock(ViolationsHandlerInterface::class), false];

        $violationsHandler = Phake::mock(ViolationsHandlerInterface::class);
        $classMetric = Phake::mock(ClassMetric::class);
        Phake::when($classMetric)->__call('get', ['violations'])->thenReturn($violationsHandler);
        Phake::when($classMetric)->__call('get', ['ccn'])->thenReturn(3);
        Phake::when($classMetric)->__call('get', ['number_operators'])->thenReturn(72);
        Phake::when($classMetric)->__call('get', ['wmc'])->thenReturn(50);
        yield 'Weight count of methods too low' => [$classMetric, $violationsHandler, false];

        $violationsHandler = Phake::mock(ViolationsHandlerInterface::class);
        $classMetric = Phake::mock(ClassMetric::class);
        Phake::when($classMetric)->__call('get', ['violations'])->thenReturn($violationsHandler);
        Phake::when($classMetric)->__call('get', ['ccn'])->thenReturn(3);
        Phake::when($classMetric)->__call('get', ['number_operators'])->thenReturn(72);
        Phake::when($classMetric)->__call('get', ['wmc'])->thenReturn(51);
        yield 'Violations (edge mode)' => [$classMetric, $violationsHandler, true];

        $violationsHandler = Phake::mock(ViolationsHandlerInterface::class);
        $classMetric = Phake::mock(ClassMetric::class);
        Phake::when($classMetric)->__call('get', ['violations'])->thenReturn($violationsHandler);
        Phake::when($classMetric)->__call('get', ['ccn'])->thenReturn(3);
        Phake::when($classMetric)->__call('get', ['number_operators'])->thenReturn(72);
        Phake::when($classMetric)->__call('get', ['wmc'])->thenReturn(993847);
        yield 'Violations (overkill)' => [$classMetric, $violationsHandler, true];
    }

    /**
     * @param IMock&Metric $metric
     * @param ViolationsHandlerInterface&IMock $violationsHandler
     * @param bool $violate
     * @return void
     */
    #[DataProvider('provideMetricToCheckIfViolationApplies')]
    public function testViolationApplies(
        IMock&Metric $metric,
        IMock&ViolationsHandlerInterface $violationsHandler,
        bool $violate
    ): void {
        $violation = new TooComplexClassCode();
        $violation->apply($metric);

        if (!$metric instanceof ClassMetric) {
            Phake::verifyNoInteraction($metric);
        }
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
This class looks really complex.

* Algorithms are complex (Total cyclomatic complexity of class is {$metric->get('ccn')})
* Component uses {$metric->get('number_operators')} operators

Maybe you should delegate some code to other objects.
EOT;
    }
}
