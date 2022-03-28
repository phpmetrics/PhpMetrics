<?php
declare(strict_types=1);

namespace Tests\Hal\Report\Json;

use Exception;
use Hal\Application\Config\ConfigBagInterface;
use Hal\Component\Output\Output;
use Hal\Exception\NotWritableJsonReportException;
use Hal\Metric\Metrics;
use Hal\Report\Json\Reporter;
use JsonException;
use Phake;
use PHPUnit\Framework\TestCase;
use function dirname;
use function file_get_contents;
use function random_int;
use function realpath;
use function shell_exec;
use const PHP_INT_MAX;

final class ReporterTest extends TestCase
{
    /**
     * @throws JsonException
     */
    public function testJsonReportIsIgnoredWhenQuietOutput(): void
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

    /**
     * @throws JsonException
     */
    public function testJsonReportIsIgnoredWhenConfigIsNotSet(): void
    {
        $metrics = Phake::mock(Metrics::class);
        $config = Phake::mock(ConfigBagInterface::class);
        $output = Phake::mock(Output::class);
        Phake::when($output)->__call('isQuiet', [])->thenReturn(false);
        Phake::when($config)->__call('get', ['report-json'])->thenReturn(null);

        $reporter = new Reporter($config, $output);
        $reporter->generate($metrics);

        Phake::verify($output)->__call('isQuiet', []);
        Phake::verify($config)->__call('get', ['report-json']);
        Phake::verifyNoOtherInteractions($output);
        Phake::verifyNoOtherInteractions($config);
        Phake::verifyNoInteraction($metrics);
    }

    /**
     * @throws Exception
     * @throws JsonException
     */
    public function testJsonReportIsNotAllowedWhenFolderIsMissing(): void
    {
        $metrics = Phake::mock(Metrics::class);
        $config = Phake::mock(ConfigBagInterface::class);
        $output = Phake::mock(Output::class);
        Phake::when($output)->__call('isQuiet', [])->thenReturn(false);
        $folder = '/tmp/this-folder-does-not-exists-' . random_int(0, PHP_INT_MAX);
        Phake::when($config)->__call('get', ['report-json'])->thenReturn($folder . '/report.json');

        $this->expectExceptionObject(NotWritableJsonReportException::noPermission($folder . '/report.json'));

        $reporter = new Reporter($config, $output);
        $reporter->generate($metrics);

        Phake::verify($output)->__call('isQuiet', []);
        Phake::verify($config)->__call('get', ['report-json']);
        Phake::verifyNoOtherInteractions($output);
        Phake::verifyNoOtherInteractions($config);
        Phake::verifyNoInteraction($metrics);
    }

    /**
     * @throws JsonException
     */
    public function testJsonReportIsNotAllowedWhenFolderIsNotWriteable(): void
    {
        $metrics = Phake::mock(Metrics::class);
        $config = Phake::mock(ConfigBagInterface::class);
        $output = Phake::mock(Output::class);
        Phake::when($output)->__call('isQuiet', [])->thenReturn(false);
        $folder = realpath(dirname(__DIR__, 2)) . '/resources/report/no-perm-json';
        Phake::when($config)->__call('get', ['report-json'])->thenReturn($folder . '/report.json');

        $this->expectExceptionObject(NotWritableJsonReportException::noPermission($folder . '/report.json'));

        $reporter = new Reporter($config, $output);
        $reporter->generate($metrics);

        Phake::verify($output)->__call('isQuiet', []);
        Phake::verify($config)->__call('get', ['report-json']);
        Phake::verifyNoOtherInteractions($output);
        Phake::verifyNoOtherInteractions($config);
        Phake::verifyNoInteraction($metrics);
    }

    /**
     * @throws JsonException
     */
    public function testJsonReport(): void
    {
        $metrics = Phake::mock(Metrics::class);
        $config = Phake::mock(ConfigBagInterface::class);
        $output = Phake::mock(Output::class);
        Phake::when($output)->__call('isQuiet', [])->thenReturn(false);
        $folder = realpath(dirname(__DIR__, 2)) . '/resources/report/json';
        shell_exec('rm -rf ' . $folder . '/report.json');
        Phake::when($config)->__call('get', ['report-json'])->thenReturn($folder . '/report.json');

        Phake::when($metrics)->__call('jsonSerialize', [])->thenReturn(['unitTest' => true]);
        $expectedJsonContent = <<<JSON
        {
            "unitTest": true
        }
        JSON;

        $reporter = new Reporter($config, $output);
        $reporter->generate($metrics);

        self::assertFileExists($folder . '/report.json');
        self::assertSame($expectedJsonContent, file_get_contents($folder . '/report.json'));

        Phake::verify($output)->__call('isQuiet', []);
        Phake::verify($config)->__call('get', ['report-json']);
        Phake::verify($metrics)->__call('jsonSerialize', []);
        Phake::verifyNoOtherInteractions($output);
        Phake::verifyNoOtherInteractions($config);
        Phake::verifyNoOtherInteractions($metrics);
        shell_exec('rm -rf ' . $folder . '/report.json');
    }
}
