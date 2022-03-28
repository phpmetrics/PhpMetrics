<?php
declare(strict_types=1);

namespace Tests\Hal\Violation\Class_;

use Generator;
use Hal\Metric\ClassMetric;
use Hal\Metric\Metric;
use Hal\Violation\Class_\Blob;
use Hal\Violation\Violation;
use Hal\Violation\ViolationsHandlerInterface;
use Phake;
use Phake\IMock;
use PHPUnit\Framework\TestCase;

final class BlobTest extends TestCase
{
    public function testViolationLevel(): void
    {
        self::assertSame(Violation::ERROR, (new Blob())->getLevel());
    }

    public function testViolationName(): void
    {
        self::assertSame('Blob / God object', (new Blob())->getName());
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
        Phake::when($classMetric)->__call('get', ['nbMethodsPublic'])->thenReturn(7);
        Phake::when($classMetric)->__call('get', ['lcom'])->thenReturn(3);
        Phake::when($classMetric)->__call('get', ['externals'])->thenReturn(['A', 'B', 'C', 'D', 'E', 'F', 'G', 'H']);
        yield 'Not enough public methods' => [$classMetric, $violationsHandler, false];

        $violationsHandler = Phake::mock(ViolationsHandlerInterface::class);
        $classMetric = Phake::mock(ClassMetric::class);
        Phake::when($classMetric)->__call('get', ['violations'])->thenReturn($violationsHandler);
        Phake::when($classMetric)->__call('get', ['nbMethodsPublic'])->thenReturn(8);
        Phake::when($classMetric)->__call('get', ['lcom'])->thenReturn(2);
        Phake::when($classMetric)->__call('get', ['externals'])->thenReturn(['A', 'B', 'C', 'D', 'E', 'F', 'G', 'H']);
        yield 'Not enough lcom' => [$classMetric, $violationsHandler, false];

        $violationsHandler = Phake::mock(ViolationsHandlerInterface::class);
        $classMetric = Phake::mock(ClassMetric::class);
        Phake::when($classMetric)->__call('get', ['violations'])->thenReturn($violationsHandler);
        Phake::when($classMetric)->__call('get', ['nbMethodsPublic'])->thenReturn(8);
        Phake::when($classMetric)->__call('get', ['lcom'])->thenReturn(3);
        Phake::when($classMetric)->__call('get', ['externals'])->thenReturn(['A', 'B', 'C', 'D', 'E', 'F', 'G']);
        yield 'Not enough externals' => [$classMetric, $violationsHandler, false];

        $violationsHandler = Phake::mock(ViolationsHandlerInterface::class);
        $classMetric = Phake::mock(ClassMetric::class);
        Phake::when($classMetric)->__call('get', ['violations'])->thenReturn($violationsHandler);
        Phake::when($classMetric)->__call('get', ['nbMethodsPublic'])->thenReturn(8);
        Phake::when($classMetric)->__call('get', ['lcom'])->thenReturn(3);
        Phake::when($classMetric)->__call('get', ['externals'])->thenReturn(['A', 'B', 'C', 'D', 'E', 'F', 'G', 'H']);
        yield 'Violations (edge mode)' => [$classMetric, $violationsHandler, true];

        $violationsHandler = Phake::mock(ViolationsHandlerInterface::class);
        $classMetric = Phake::mock(ClassMetric::class);
        Phake::when($classMetric)->__call('get', ['violations'])->thenReturn($violationsHandler);
        Phake::when($classMetric)->__call('get', ['nbMethodsPublic'])->thenReturn(99);
        Phake::when($classMetric)->__call('get', ['lcom'])->thenReturn(99);
        Phake::when($classMetric)->__call('get', ['externals'])->thenReturn(
            ['A', 'B', 'C', 'D', 'E', 'F', 'G', 'H', 'I', 'J', 'K', 'L', 'M', 'N', 'O', 'P', 'Q', 'R', 'S', 'T', 'U']
        );
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
        $violation = new Blob();
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
A blob object (or "god class") does not follow the Single responsibility principle.

* object has lot of public methods ({$metric->get('nbMethodsPublic')}, excluding getters and setters)
* object has a high Lack of cohesion of methods (LCOM={$metric->get('lcom')})
* object knows everything (and use lot of external classes)

Maybe you should reducing the number of methods splitting this object in many sub objects.
EOT;
    }
}
