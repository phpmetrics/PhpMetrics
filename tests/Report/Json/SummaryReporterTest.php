<?php
declare(strict_types=1);

namespace Tests\Hal\Report\Json;

use Hal\Metric\Metrics;
use Hal\Report\Json\SummaryReporter;
use Hal\Report\SummaryProviderInterface;
use JsonException;
use Phake;
use PHPUnit\Framework\TestCase;
use function dirname;
use function file_get_contents;
use function realpath;
use function unlink;

final class SummaryReporterTest extends TestCase
{
    /**
     * @throws JsonException
     */
    public function testICantGenerateReportIfNoFileDefined(): void
    {
        $summary = Phake::mock(SummaryProviderInterface::class);
        $metrics = Phake::mock(Metrics::class);

        Phake::when($summary)->__call('getReportFile', [])->thenReturn(false);

        (new SummaryReporter($summary))->generate($metrics);

        Phake::verify($summary)->__call('getReportFile', []);
        Phake::verifyNoOtherInteractions($summary);
        Phake::verifyNoInteraction($metrics);
    }

    /**
     * @throws JsonException
     */
    public function testICanGenerateReportWhenTargetFileDefined(): void
    {
        $summary = Phake::mock(SummaryProviderInterface::class);
        $metrics = Phake::mock(Metrics::class);

        $reportFile = realpath(dirname(__DIR__, 2)) . '/resources/report/json/report.json';
        Phake::when($summary)->__call('getReportFile', [])->thenReturn($reportFile);
        Phake::when($summary)->__call('summarize', [$metrics])->thenDoNothing();
        Phake::when($summary)->__call('getReport', [])->thenReturn(['test' => true]);

        (new SummaryReporter($summary))->generate($metrics);

        Phake::verify($summary)->__call('getReportFile', []);
        Phake::verify($summary)->__call('summarize', [$metrics]);
        Phake::verify($summary)->__call('getReport', []);
        Phake::verifyNoOtherInteractions($summary);
        Phake::verifyNoInteraction($metrics);

        self::assertFileExists($reportFile);
        $expectedContent = <<<'JSON'
        {
            "test": true
        }
        JSON;
        self::assertSame($expectedContent, file_get_contents($reportFile));

        unlink($reportFile);
    }
}
