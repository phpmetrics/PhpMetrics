<?php
declare(strict_types=1);

namespace Tests\Hal\Violation\Search;

use Generator;
use Hal\Metric\ClassMetric;
use Hal\Metric\Metric;
use Hal\Violation\Search\SearchShouldNotBeFoundPrinciple;
use Hal\Violation\Violation;
use Hal\Violation\ViolationsHandlerInterface;
use Phake;
use Phake\IMock;
use PHPUnit\Framework\TestCase;
use function count;

final class SearchShouldNotBeFoundPrincipleTest extends TestCase
{
    public function testViolationLevel(): void
    {
        self::assertSame(Violation::CRITICAL, (new SearchShouldNotBeFoundPrinciple())->getLevel());
    }

    /**
     * @return Generator<string, array{0: IMock&Metric, 1: IMock&ViolationsHandlerInterface, 2: bool, 3: null|string}>
     */
    public function provideMetricToCheckIfViolationApplies(): Generator
    {
        yield 'Invalid metric' => [
            [Phake::mock(Metric::class)],
            Phake::mock(ViolationsHandlerInterface::class),
            false,
            null
        ];

        $violationsHandler = Phake::mock(ViolationsHandlerInterface::class);
        $classMetric = Phake::mock(ClassMetric::class);
        Phake::when($classMetric)->__call('get', ['was-not-expected'])->thenReturn(false);
        Phake::when($classMetric)->__call('get', ['violations'])->thenReturn($violationsHandler);
        yield 'Was expected' => [[$classMetric], $violationsHandler, false, null];

        $violationsHandler = Phake::mock(ViolationsHandlerInterface::class);
        $classMetrics = [
            Phake::mock(ClassMetric::class),
            Phake::mock(ClassMetric::class),
            Phake::mock(ClassMetric::class),
        ];
        Phake::when($classMetrics[0])->__call('get', ['was-not-expected'])->thenReturn(true);
        Phake::when($classMetrics[0])->__call('get', ['was-not-expected-by'])->thenReturn(['A']);
        Phake::when($classMetrics[0])->__call('get', ['violations'])->thenReturn($violationsHandler);
        Phake::when($classMetrics[1])->__call('get', ['was-not-expected'])->thenReturn(true);
        Phake::when($classMetrics[1])->__call('get', ['was-not-expected-by'])->thenReturn(['B', 'A']);
        Phake::when($classMetrics[1])->__call('get', ['violations'])->thenReturn($violationsHandler);
        Phake::when($classMetrics[2])->__call('get', ['was-not-expected'])->thenReturn(true);
        Phake::when($classMetrics[2])->__call('get', ['was-not-expected-by'])->thenReturn([]);
        Phake::when($classMetrics[2])->__call('get', ['violations'])->thenReturn($violationsHandler);
        yield 'Was not expected' => [$classMetrics, $violationsHandler, true, 'A, B'];
    }

    /**
     * @dataProvider provideMetricToCheckIfViolationApplies
     * @param array<IMock&Metric> $metricList
     * @param ViolationsHandlerInterface&IMock $violationsHandler
     * @param bool $violate
     * @param null|string $expectedName
     * @return void
     */
    //#[DataProvider('provideMetricToCheckIfViolationApplies')] TODO: PHPUnit 10
    public function testViolationApplies(
        array $metricList,
        IMock&ViolationsHandlerInterface $violationsHandler,
        bool $violate,
        null|string $expectedName
    ): void {
        $violation = new SearchShouldNotBeFoundPrinciple();
        foreach ($metricList as $metric) {
            $violation->apply($metric);
        }

        if (false === $violate) {
            Phake::verifyNoInteraction($violationsHandler);
            return;
        }

        foreach ($metricList as $metric) {
            Phake::verify($metric)->__call('get', ['violations']);
        }
        Phake::verify($violationsHandler, Phake::times(count($metricList)))->__call('add', [$violation]);
        Phake::verifyNoOtherInteractions($violationsHandler);
        self::assertSame($this->getExpectedDescription(), $violation->getDescription());
        self::assertSame($expectedName, $violation->getName());
    }

    /**
     * Returns the expected description of the current violation based on the values stored in the given metrics.
     *
     * @return string
     */
    private function getExpectedDescription(): string
    {
        return 'According configuration, this component is not expected to be found in the code.';
    }
}
