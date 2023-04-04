<?php
declare(strict_types=1);

namespace Tests\Hal\Report\Csv;

use Hal\Application\Config\ConfigBagInterface;
use Hal\Component\File\WriterInterface;
use Hal\Component\Output\Output;
use Hal\Exception\NotWritableCsvReportException;
use Hal\Metric\ClassMetric;
use Hal\Metric\Metrics;
use Hal\Metric\Registry;
use Hal\Report\Csv\Reporter;
use Phake;
use PHPUnit\Framework\TestCase;

final class ReporterTest extends TestCase
{
    public function testCsvReportIsIgnoredWhenQuietOutput(): void
    {
        $metrics = Phake::mock(Metrics::class);
        $config = Phake::mock(ConfigBagInterface::class);
        $output = Phake::mock(Output::class);
        $fileWriter = Phake::mock(WriterInterface::class);
        Phake::when($output)->__call('isQuiet', [])->thenReturn(true);

        $reporter = new Reporter($config, $output, $fileWriter);
        $reporter->generate($metrics);

        Phake::verify($output)->__call('isQuiet', []);
        Phake::verifyNoOtherInteractions($output);
        Phake::verifyNoInteraction($config);
        Phake::verifyNoInteraction($metrics);
        Phake::verifyNoInteraction($fileWriter);
    }

    public function testCsvReportIsIgnoredWhenConfigIsNotSet(): void
    {
        $metrics = Phake::mock(Metrics::class);
        $config = Phake::mock(ConfigBagInterface::class);
        $output = Phake::mock(Output::class);
        $fileWriter = Phake::mock(WriterInterface::class);
        Phake::when($output)->__call('isQuiet', [])->thenReturn(false);
        Phake::when($config)->__call('get', ['report-csv'])->thenReturn(null);

        $reporter = new Reporter($config, $output, $fileWriter);
        $reporter->generate($metrics);

        Phake::verify($output)->__call('isQuiet', []);
        Phake::verify($config)->__call('get', ['report-csv']);
        Phake::verifyNoOtherInteractions($output);
        Phake::verifyNoOtherInteractions($config);
        Phake::verifyNoInteraction($metrics);
        Phake::verifyNoInteraction($fileWriter);
    }

    public function testCsvReportIsNotAllowedWhenFolderIsMissing(): void
    {
        $metrics = Phake::mock(Metrics::class);
        $config = Phake::mock(ConfigBagInterface::class);
        $output = Phake::mock(Output::class);
        $fileWriter = Phake::mock(WriterInterface::class);
        Phake::when($output)->__call('isQuiet', [])->thenReturn(false);
        $file = '/test/report/report.csv';
        Phake::when($config)->__call('get', ['report-csv'])->thenReturn($file);
        Phake::when($fileWriter)->__call('exists', ['/test/report'])->thenReturn(false);

        $this->expectExceptionObject(NotWritableCsvReportException::noPermission($file));

        $reporter = new Reporter($config, $output, $fileWriter);
        $reporter->generate($metrics);

        Phake::verify($output)->__call('isQuiet', []);
        Phake::verify($config)->__call('get', ['report-csv']);
        Phake::verify($fileWriter)->__call('exists', ['/test/report']);
        Phake::verifyNoOtherInteractions($output);
        Phake::verifyNoOtherInteractions($config);
        Phake::verifyNoOtherInteractions($fileWriter);
        Phake::verifyNoInteraction($metrics);
    }

    public function testCsvReportIsNotAllowedWhenFolderIsNotWriteable(): void
    {
        $metrics = Phake::mock(Metrics::class);
        $config = Phake::mock(ConfigBagInterface::class);
        $output = Phake::mock(Output::class);
        $fileWriter = Phake::mock(WriterInterface::class);
        Phake::when($output)->__call('isQuiet', [])->thenReturn(false);
        $file = '/test/report/report.csv';
        Phake::when($config)->__call('get', ['report-csv'])->thenReturn($file);
        Phake::when($fileWriter)->__call('exists', ['/test/report'])->thenReturn(true);
        Phake::when($fileWriter)->__call('isWritable', ['/test/report'])->thenReturn(false);

        $this->expectExceptionObject(NotWritableCsvReportException::noPermission($file));

        $reporter = new Reporter($config, $output, $fileWriter);
        $reporter->generate($metrics);

        Phake::verify($output)->__call('isQuiet', []);
        Phake::verify($config)->__call('get', ['report-csv']);
        Phake::verify($fileWriter)->__call('exists', ['/test/report']);
        Phake::verify($fileWriter)->__call('isWritable', ['/test/report']);
        Phake::verifyNoOtherInteractions($output);
        Phake::verifyNoOtherInteractions($config);
        Phake::verifyNoOtherInteractions($fileWriter);
        Phake::verifyNoInteraction($metrics);
    }

    public function testCsvReport(): void
    {
        $metrics = Phake::mock(Metrics::class);
        $config = Phake::mock(ConfigBagInterface::class);
        $output = Phake::mock(Output::class);
        $fileWriter = Phake::mock(WriterInterface::class);
        Phake::when($output)->__call('isQuiet', [])->thenReturn(false);
        $file = '/test/report/report.csv';
        Phake::when($config)->__call('get', ['report-csv'])->thenReturn($file);
        Phake::when($fileWriter)->__call('exists', ['/test/report'])->thenReturn(true);
        Phake::when($fileWriter)->__call('isWritable', ['/test/report'])->thenReturn(true);
        Phake::when($fileWriter)->__call('writeCsv', [Phake::anyParameters()])->thenDoNothing();

        $allMetrics = Registry::allForStructures();
        $expectedCsv = [];
        $classMetrics = [
            Phake::mock(ClassMetric::class),
            Phake::mock(ClassMetric::class),
            Phake::mock(ClassMetric::class),
        ];
        foreach ($classMetrics as $i => $classMetric) {
            foreach ($allMetrics as $k => $metricKey) {
                // Every 3 metrics, a non-scalar value is set so the expected string in CSV is "N/A".
                Phake::when($classMetric)->__call('get', [$metricKey])->thenReturn($k % 3 ? 42 : []);
                $expectedCsv[$i][$k] = ($k % 3 ? 42 : 'N/A');
            }
        }
        Phake::when($metrics)->__call('getClassMetrics', [])->thenReturn($classMetrics);

        $reporter = new Reporter($config, $output, $fileWriter);
        $reporter->generate($metrics);

        Phake::verify($output)->__call('isQuiet', []);
        Phake::verify($config)->__call('get', ['report-csv']);
        Phake::verify($fileWriter)->__call('exists', ['/test/report']);
        Phake::verify($fileWriter)->__call('isWritable', ['/test/report']);
        Phake::verify($fileWriter)->__call('writeCsv', [$file, $expectedCsv, $allMetrics]);
        Phake::verify($metrics)->__call('getClassMetrics', []);
        Phake::verifyNoOtherInteractions($output);
        Phake::verifyNoOtherInteractions($config);
        Phake::verifyNoOtherInteractions($fileWriter);
        Phake::verifyNoOtherInteractions($metrics);
    }
}
