<?php
declare(strict_types=1);

namespace Tests\Hal\Report\Csv;

use Exception;
use Hal\Application\Config\ConfigBagInterface;
use Hal\Component\Output\Output;
use Hal\Exception\NotWritableCsvReportException;
use Hal\Metric\ClassMetric;
use Hal\Metric\Metrics;
use Hal\Metric\Registry;
use Hal\Report\Csv\Reporter;
use Phake;
use PHPUnit\Framework\TestCase;
use function dirname;
use function file_get_contents;
use function implode;
use function random_int;
use function realpath;
use function rtrim;
use function shell_exec;
use const PHP_EOL;
use const PHP_INT_MAX;

final class ReporterTest extends TestCase
{
    public function testCsvReportIsIgnoredWhenQuietOutput(): void
    {
        $metrics = Phake::mock(Metrics::class);
        $config = Phake::mock(ConfigBagInterface::class);
        $output = Phake::mock(Output::class);
        Phake::when($output)->__call('isQuiet', [])->thenReturn(true);

        $reporter = new Reporter($config, $output);
        $reporter->generate($metrics);

        Phake::verify($output)->__call('isQuiet', []);
        Phake::verifyNoOtherInteractions($output);
        Phake::verifyNoInteraction($config);
        Phake::verifyNoInteraction($metrics);
    }

    public function testCsvReportIsIgnoredWhenConfigIsNotSet(): void
    {
        $metrics = Phake::mock(Metrics::class);
        $config = Phake::mock(ConfigBagInterface::class);
        $output = Phake::mock(Output::class);
        Phake::when($output)->__call('isQuiet', [])->thenReturn(false);
        Phake::when($config)->__call('get', ['report-csv'])->thenReturn(null);

        $reporter = new Reporter($config, $output);
        $reporter->generate($metrics);

        Phake::verify($output)->__call('isQuiet', []);
        Phake::verify($config)->__call('get', ['report-csv']);
        Phake::verifyNoOtherInteractions($output);
        Phake::verifyNoOtherInteractions($config);
        Phake::verifyNoInteraction($metrics);
    }

    /**
     * @throws Exception
     */
    public function testCsvReportIsNotAllowedWhenFolderIsMissing(): void
    {
        $metrics = Phake::mock(Metrics::class);
        $config = Phake::mock(ConfigBagInterface::class);
        $output = Phake::mock(Output::class);
        Phake::when($output)->__call('isQuiet', [])->thenReturn(false);
        $folder = '/tmp/this-folder-does-not-exists-' . random_int(0, PHP_INT_MAX);
        Phake::when($config)->__call('get', ['report-csv'])->thenReturn($folder . '/report.csv');

        $this->expectExceptionObject(NotWritableCsvReportException::noPermission($folder . '/report.csv'));

        $reporter = new Reporter($config, $output);
        $reporter->generate($metrics);

        Phake::verify($output)->__call('isQuiet', []);
        Phake::verify($config)->__call('get', ['report-csv']);
        Phake::verifyNoOtherInteractions($output);
        Phake::verifyNoOtherInteractions($config);
        Phake::verifyNoInteraction($metrics);
    }

    public function testCsvReportIsNotAllowedWhenFolderIsNotWriteable(): void
    {
        $metrics = Phake::mock(Metrics::class);
        $config = Phake::mock(ConfigBagInterface::class);
        $output = Phake::mock(Output::class);
        Phake::when($output)->__call('isQuiet', [])->thenReturn(false);
        $folder = realpath(dirname(__DIR__, 2)) . '/resources/report/no-perm-csv';
        Phake::when($config)->__call('get', ['report-csv'])->thenReturn($folder . '/report.csv');

        $this->expectExceptionObject(NotWritableCsvReportException::noPermission($folder . '/report.csv'));

        $reporter = new Reporter($config, $output);
        $reporter->generate($metrics);

        Phake::verify($output)->__call('isQuiet', []);
        Phake::verify($config)->__call('get', ['report-csv']);
        Phake::verifyNoOtherInteractions($output);
        Phake::verifyNoOtherInteractions($config);
        Phake::verifyNoInteraction($metrics);
    }

    public function testCsvReport(): void
    {
        $metrics = Phake::mock(Metrics::class);
        $config = Phake::mock(ConfigBagInterface::class);
        $output = Phake::mock(Output::class);
        Phake::when($output)->__call('isQuiet', [])->thenReturn(false);
        $folder = realpath(dirname(__DIR__, 2)) . '/resources/report/csv';
        shell_exec('rm -rf ' . $folder . '/report.csv');
        Phake::when($config)->__call('get', ['report-csv'])->thenReturn($folder . '/report.csv');

        $allMetrics = Registry::allForStructures();
        $expectedCsvContent = implode(',', $allMetrics) . PHP_EOL;
        $classMetrics = [
            Phake::mock(ClassMetric::class),
            Phake::mock(ClassMetric::class),
            Phake::mock(ClassMetric::class),
        ];
        foreach ($classMetrics as $classMetric) {
            foreach ($allMetrics as $k => $metricKey) {
                // Every 3 metrics, a non-scalar value is set so the expected string in CSV is "N/A".
                Phake::when($classMetric)->__call('get', [$metricKey])->thenReturn($k % 3 ? 42 : []);
                $expectedCsvContent .= ($k % 3 ? 42 : 'N/A') . ',';
            }
            $expectedCsvContent = rtrim($expectedCsvContent, ',') . PHP_EOL;
        }

        Phake::when($metrics)->__call('getClassMetrics', [])->thenReturn($classMetrics);

        $reporter = new Reporter($config, $output);
        $reporter->generate($metrics);

        self::assertFileExists($folder . '/report.csv');
        self::assertSame($expectedCsvContent, file_get_contents($folder . '/report.csv'));

        Phake::verify($output)->__call('isQuiet', []);
        Phake::verify($config)->__call('get', ['report-csv']);
        Phake::verify($metrics)->__call('getClassMetrics', []);
        Phake::verifyNoOtherInteractions($output);
        Phake::verifyNoOtherInteractions($config);
        Phake::verifyNoOtherInteractions($metrics);
        shell_exec('rm -rf ' . $folder . '/report.csv');
    }
}
