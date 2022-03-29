<?php
declare(strict_types=1);

namespace Tests\Hal\Report\Cli;

use Hal\Component\Output\Output;
use Hal\Metric\Metrics;
use Hal\Report\Cli\Reporter;
use Hal\Report\SummaryProviderInterface;
use Phake;
use PHPUnit\Framework\TestCase;

final class ReporterTest extends TestCase
{
    public function testCliReportIsIgnoredWhenReportDisabled(): void
    {
        $metrics = Phake::mock(Metrics::class);
        $summaryProvider = Phake::mock(SummaryProviderInterface::class);
        Phake::when($summaryProvider)->__call('getReportFile', [])->thenReturn(false);
        $output = Phake::mock(Output::class);

        $reporter = new Reporter($summaryProvider, $output);
        $reporter->generate($metrics);

        Phake::verify($summaryProvider)->__call('getReportFile', []);
        Phake::verifyNoInteraction($output);
        Phake::verifyNoInteraction($metrics);
        Phake::verifyNoOtherInteractions($summaryProvider);
    }

    public function testCliReportOKWhenReportEnabled(): void
    {
        $metrics = Phake::mock(Metrics::class);
        $summaryProvider = Phake::mock(SummaryProviderInterface::class);
        Phake::when($summaryProvider)->__call('getReportFile', [])->thenReturn(true);
        Phake::when($summaryProvider)->__call('summarize', [$metrics])->thenDoNothing();
        Phake::when($summaryProvider)->__call('getReport', [])->thenReturn('string');
        $output = Phake::mock(Output::class);
        Phake::when($output)->__call('write', [Phake::anyParameters()])->thenDoNothing();

        $reporter = new Reporter($summaryProvider, $output);
        $reporter->generate($metrics);

        Phake::verify($summaryProvider)->__call('getReportFile', []);
        Phake::verify($summaryProvider)->__call('summarize', [$metrics]);
        Phake::verify($summaryProvider)->__call('getReport', []);
        Phake::verify($output)->__call('write', ['string']);

        Phake::verifyNoInteraction($metrics);
        Phake::verifyNoOtherInteractions($output);
        Phake::verifyNoOtherInteractions($summaryProvider);
    }
}
