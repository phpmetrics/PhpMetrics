<?php
declare(strict_types=1);

namespace Tests\Hal\Report\Json;

use Hal\Application\Config\ConfigBagInterface;
use Hal\Component\File\WriterInterface;
use Hal\Component\Output\Output;
use Hal\Exception\NotWritableJsonReportException;
use Hal\Metric\Metrics;
use Hal\Report\Json\Reporter;
use Phake;
use PHPUnit\Framework\TestCase;

final class ReporterTest extends TestCase
{
    public function testJsonReportIsIgnoredWhenQuietOutput(): void
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
        Phake::verifyNoInteraction($fileWriter);
        Phake::verifyNoInteraction($metrics);
    }

    public function testJsonReportIsIgnoredWhenConfigIsNotSet(): void
    {
        $metrics = Phake::mock(Metrics::class);
        $config = Phake::mock(ConfigBagInterface::class);
        $output = Phake::mock(Output::class);
        $fileWriter = Phake::mock(WriterInterface::class);
        Phake::when($output)->__call('isQuiet', [])->thenReturn(false);
        Phake::when($config)->__call('get', ['report-json'])->thenReturn(null);

        $reporter = new Reporter($config, $output, $fileWriter);
        $reporter->generate($metrics);

        Phake::verify($output)->__call('isQuiet', []);
        Phake::verify($config)->__call('get', ['report-json']);
        Phake::verifyNoOtherInteractions($output);
        Phake::verifyNoOtherInteractions($config);
        Phake::verifyNoInteraction($fileWriter);
        Phake::verifyNoInteraction($metrics);
    }

    public function testJsonReportIsNotAllowedWhenFolderIsMissing(): void
    {
        $metrics = Phake::mock(Metrics::class);
        $config = Phake::mock(ConfigBagInterface::class);
        $output = Phake::mock(Output::class);
        $fileWriter = Phake::mock(WriterInterface::class);
        Phake::when($output)->__call('isQuiet', [])->thenReturn(false);
        $file = '/test/report/report.json';
        Phake::when($config)->__call('get', ['report-json'])->thenReturn($file);
        Phake::when($fileWriter)->__call('exists', ['/test/report'])->thenReturn(false);

        $this->expectExceptionObject(NotWritableJsonReportException::noPermission($file));

        $reporter = new Reporter($config, $output, $fileWriter);
        $reporter->generate($metrics);

        Phake::verify($output)->__call('isQuiet', []);
        Phake::verify($config)->__call('get', ['report-json']);
        Phake::verify($fileWriter)->__call('exists', ['/test/report']);
        Phake::verifyNoOtherInteractions($output);
        Phake::verifyNoOtherInteractions($config);
        Phake::verifyNoInteraction($metrics);
    }

    public function testJsonReportIsNotAllowedWhenFolderIsNotWriteable(): void
    {
        $metrics = Phake::mock(Metrics::class);
        $config = Phake::mock(ConfigBagInterface::class);
        $output = Phake::mock(Output::class);
        $fileWriter = Phake::mock(WriterInterface::class);
        Phake::when($output)->__call('isQuiet', [])->thenReturn(false);
        $file = '/test/report/report.json';
        Phake::when($config)->__call('get', ['report-json'])->thenReturn($file);
        Phake::when($fileWriter)->__call('exists', ['/test/report'])->thenReturn(true);
        Phake::when($fileWriter)->__call('isWritable', ['/test/report'])->thenReturn(false);

        $this->expectExceptionObject(NotWritableJsonReportException::noPermission($file));

        $reporter = new Reporter($config, $output, $fileWriter);
        $reporter->generate($metrics);

        Phake::verify($output)->__call('isQuiet', []);
        Phake::verify($config)->__call('get', ['report-json']);
        Phake::verify($fileWriter)->__call('exists', ['/test/report']);
        Phake::verify($fileWriter)->__call('isWritable', ['/test/report']);
        Phake::verifyNoOtherInteractions($output);
        Phake::verifyNoOtherInteractions($config);
        Phake::verifyNoOtherInteractions($fileWriter);
        Phake::verifyNoInteraction($metrics);
    }

    public function testJsonReport(): void
    {
        $metrics = Phake::mock(Metrics::class);
        $config = Phake::mock(ConfigBagInterface::class);
        $output = Phake::mock(Output::class);
        $fileWriter = Phake::mock(WriterInterface::class);
        Phake::when($output)->__call('isQuiet', [])->thenReturn(false);
        $file = '/test/report/report.json';
        Phake::when($config)->__call('get', ['report-json'])->thenReturn($file);
        Phake::when($fileWriter)->__call('exists', ['/test/report'])->thenReturn(true);
        Phake::when($fileWriter)->__call('isWritable', ['/test/report'])->thenReturn(true);
        Phake::when($fileWriter)->__call('writePrettyJson', [Phake::anyParameters()])->thenDoNothing();

        $reporter = new Reporter($config, $output, $fileWriter);
        $reporter->generate($metrics);

        Phake::verify($output)->__call('isQuiet', []);
        Phake::verify($config)->__call('get', ['report-json']);
        Phake::verify($fileWriter)->__call('exists', ['/test/report']);
        Phake::verify($fileWriter)->__call('isWritable', ['/test/report']);
        Phake::verify($fileWriter)->__call('writePrettyJson', [$file, $metrics]);
        Phake::verifyNoOtherInteractions($output);
        Phake::verifyNoOtherInteractions($config);
        Phake::verifyNoOtherInteractions($fileWriter);
        Phake::verifyNoInteraction($metrics);
    }
}
