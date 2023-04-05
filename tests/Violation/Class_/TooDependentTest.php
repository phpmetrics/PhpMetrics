<?php
declare(strict_types=1);

namespace Tests\Hal\Violation\Class_;

use Generator;
use Hal\Metric\ClassMetric;
use Hal\Metric\Metric;
use Hal\Violation\Class_\TooDependent;
use Hal\Violation\Violation;
use Hal\Violation\ViolationsHandlerInterface;
use Phake;
use Phake\IMock;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

final class TooDependentTest extends TestCase
{
    public function testViolationLevel(): void
    {
        self::assertSame(Violation::INFO, (new TooDependent())->getLevel());
    }

    public function testViolationName(): void
    {
        self::assertSame('Too dependent', (new TooDependent())->getName());
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
        Phake::when($classMetric)->__call('get', ['efferentCoupling'])->thenReturn(19);
        yield 'Number of dependencies too low' => [$classMetric, $violationsHandler, false];

        $violationsHandler = Phake::mock(ViolationsHandlerInterface::class);
        $classMetric = Phake::mock(ClassMetric::class);
        Phake::when($classMetric)->__call('get', ['violations'])->thenReturn($violationsHandler);
        Phake::when($classMetric)->__call('get', ['efferentCoupling'])->thenReturn(20);
        yield 'Violations (edge mode)' => [$classMetric, $violationsHandler, true];

        $violationsHandler = Phake::mock(ViolationsHandlerInterface::class);
        $classMetric = Phake::mock(ClassMetric::class);
        Phake::when($classMetric)->__call('get', ['violations'])->thenReturn($violationsHandler);
        Phake::when($classMetric)->__call('get', ['efferentCoupling'])->thenReturn(193874);
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
        $violation = new TooDependent();
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
This class looks use really high number of components.

* Efferent coupling is {$metric->get('efferentCoupling')}, so this class uses {$metric->get('efferentCoupling')} different external components.

Maybe you should check why this class has lot of dependencies.
EOT;
    }
}
