<?php
declare(strict_types=1);

namespace Tests\Hal\Report\Json;

use Hal\Component\File\WriterInterface;
use Hal\Metric\Metrics;
use Hal\Report\Json\SummaryReporter;
use Hal\Report\SummaryProviderInterface;
use Phake;
use PHPUnit\Framework\TestCase;

final class SummaryReporterTest extends TestCase
{
    public function testICantGenerateReportIfNoFileDefined(): void
    {
        $summary = Phake::mock(SummaryProviderInterface::class);
        $metrics = Phake::mock(Metrics::class);
        $fileWriter = Phake::mock(WriterInterface::class);

        Phake::when($summary)->__call('getReportFile', [])->thenReturn(false);

        (new SummaryReporter($summary, $fileWriter))->generate($metrics);

        Phake::verify($summary)->__call('getReportFile', []);
        Phake::verifyNoOtherInteractions($summary);
        Phake::verifyNoInteraction($fileWriter);
        Phake::verifyNoInteraction($metrics);
    }

    public function testICanGenerateReportWhenTargetFileDefined(): void
    {
        $summary = Phake::mock(SummaryProviderInterface::class);
        $metrics = Phake::mock(Metrics::class);
        $fileWriter = Phake::mock(WriterInterface::class);

        $reportFile = '/test/report/summary.json';
        Phake::when($summary)->__call('getReportFile', [])->thenReturn($reportFile);
        Phake::when($summary)->__call('summarize', [$metrics])->thenDoNothing();
        Phake::when($summary)->__call('getReport', [])->thenReturn(['test' => true]);
        Phake::when($fileWriter)->__call('writePrettyJson', [Phake::anyParameters()])->thenDoNothing();

        (new SummaryReporter($summary, $fileWriter))->generate($metrics);

        Phake::verify($summary)->__call('getReportFile', []);
        Phake::verify($summary)->__call('summarize', [$metrics]);
        Phake::verify($summary)->__call('getReport', []);
        Phake::verify($fileWriter)->__call('writePrettyJson', [$reportFile, ['test' => true]]);
        Phake::verifyNoOtherInteractions($summary);
        Phake::verifyNoOtherInteractions($fileWriter);
        Phake::verifyNoInteraction($metrics);
    }
}
