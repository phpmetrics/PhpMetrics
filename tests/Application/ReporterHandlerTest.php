<?php
declare(strict_types=1);

namespace Tests\Hal\Application;

use Hal\Application\ReporterHandler;
use Hal\Metric\Metrics;
use Hal\Report\ReporterInterface;
use Phake;
use PHPUnit\Framework\TestCase;
use function array_map;

final class ReporterHandlerTest extends TestCase
{
    public function testICanRunReporterHandler(): void
    {
        $mocksReporter = [
            Phake::mock(ReporterInterface::class),
            Phake::mock(ReporterInterface::class),
            Phake::mock(ReporterInterface::class),
            Phake::mock(ReporterInterface::class),
        ];
        $mockMetrics = Phake::mock(Metrics::class);

        $reporterHandler = new ReporterHandler(...$mocksReporter);
        array_map(static function (Phake\IMock $mock) use ($mockMetrics): void {
            Phake::when($mock)->__call('generate', [$mockMetrics])->thenDoNothing();
        }, $mocksReporter);

        $reporterHandler->report($mockMetrics);

        array_map(static function (Phake\IMock $mock) use ($mockMetrics): void {
            Phake::verify($mock)->__call('generate', [$mockMetrics]);
            Phake::verifyNoOtherInteractions($mock);
        }, $mocksReporter);
        Phake::verifyNoOtherInteractions($mockMetrics);
    }
}
