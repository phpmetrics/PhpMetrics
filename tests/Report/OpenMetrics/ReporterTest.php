<?php
declare(strict_types=1);

namespace Tests\Hal\Report\OpenMetrics;

use Hal\Component\File\WriterInterface;
use Hal\Metric\Metrics;
use Hal\Report\OpenMetrics\Reporter;
use Hal\Report\SummaryProviderInterface;
use Phake;
use PHPUnit\Framework\TestCase;

final class ReporterTest extends TestCase
{
    public function testOpenMetricsReportIsIgnoredWhenReportDisabled(): void
    {
        $metrics = Phake::mock(Metrics::class);
        $summaryProvider = Phake::mock(SummaryProviderInterface::class);
        Phake::when($summaryProvider)->__call('getReportFile', [])->thenReturn(false);
        $fileWriter = Phake::mock(WriterInterface::class);

        $reporter = new Reporter($summaryProvider, $fileWriter);
        $reporter->generate($metrics);

        Phake::verify($summaryProvider)->__call('getReportFile', []);
        Phake::verifyNoInteraction($metrics);
        Phake::verifyNoOtherInteractions($summaryProvider);
        Phake::verifyNoInteraction($fileWriter);
    }

    public function testOpenMetricsReportOKWhenReportEnabled(): void
    {
        $metrics = Phake::mock(Metrics::class);
        $summaryProvider = Phake::mock(SummaryProviderInterface::class);
        $file = '/test/report/report.txt';
        Phake::when($summaryProvider)->__call('getReportFile', [])->thenReturn($file);
        Phake::when($summaryProvider)->__call('summarize', [$metrics])->thenDoNothing();
        Phake::when($summaryProvider)->__call('getReport', [])->thenReturn('string');
        $fileWriter = Phake::mock(WriterInterface::class);
        Phake::when($fileWriter)->__call('write', [Phake::anyParameters()])->thenDoNothing();

        $reporter = new Reporter($summaryProvider, $fileWriter);
        $reporter->generate($metrics);

        Phake::verify($summaryProvider)->__call('getReportFile', []);
        Phake::verify($summaryProvider)->__call('summarize', [$metrics]);
        Phake::verify($summaryProvider)->__call('getReport', []);
        Phake::verify($fileWriter)->__call('write', [$file, 'string']);

        Phake::verifyNoInteraction($metrics);
        Phake::verifyNoOtherInteractions($fileWriter);
        Phake::verifyNoOtherInteractions($summaryProvider);
    }
}
