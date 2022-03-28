<?php
declare(strict_types=1);

namespace Tests\Hal\Violation;

use Hal\Metric\Metric;
use Hal\Metric\Metrics;
use Hal\Violation\Violation;
use Hal\Violation\ViolationParser;
use Hal\Violation\ViolationsHandlerInterface;
use Phake;
use Phake\IMock;
use PHPUnit\Framework\TestCase;
use function array_map;

final class ViolationParserTest extends TestCase
{
    public function testICanAddViolationsAndLoopOnThem(): void
    {
        $metricsMock = Phake::mock(Metrics::class);
        $metricMocks = [
            Phake::mock(Metric::class),
            Phake::mock(Metric::class),
            Phake::mock(Metric::class),
            Phake::mock(Metric::class),
            Phake::mock(Metric::class),
        ];
        Phake::when($metricsMock)->__call('all', [])->thenReturn($metricMocks);
        $violationsHandlerCollector = [];
        foreach ($metricMocks as $metricMock) {
            Phake::when($metricMock)->__call('set', ['violations', Phake::ignoreRemaining()])->thenReturnCallback(
                static function (
                    string $name,
                    ViolationsHandlerInterface $violationsHandler
                ) use (&$violationsHandlerCollector): void {
                    $violationsHandlerCollector[] = $violationsHandler;
                }
            );
        }
        $violations = [
            Phake::mock(Violation::class),
            Phake::mock(Violation::class),
            Phake::mock(Violation::class),
            Phake::mock(Violation::class),
        ];
        foreach ($violations as $violation) {
            foreach ($metricMocks as $metricMock) {
                Phake::when($violation)->__call('apply', [$metricMock])->thenDoNothing();
            }
        }

        $violationParser = new ViolationParser(...$violations);
        $violationParser->apply($metricsMock);

        array_map(
            static function (IMock&Metric $metricMock, ViolationsHandlerInterface $violationsHandler): void {
                Phake::verify($metricMock)->__call('set', ['violations', $violationsHandler]);
                Phake::verifyNoOtherInteractions($metricMock);
            },
            $metricMocks,
            $violationsHandlerCollector
        );
        foreach ($violations as $violation) {
            foreach ($metricMocks as $metricMock) {
                Phake::verify($violation)->__call('apply', [$metricMock]);
            }
            Phake::verifyNoOtherInteractions($violation);
        }
        Phake::verify($metricsMock)->__call('all', []);
        Phake::verifyNoOtherInteractions($metricsMock);
    }
}
