<?php
declare(strict_types=1);

namespace Tests\Hal\Application;

use Exception;
use Hal\Application\AnalyzerInterface;
use Hal\Application\PhpMetrics;
use Hal\Application\ReporterHandlerInterface;
use Hal\Component\Output\Output;
use Hal\Metric\Metrics;
use Phake;
use PHPUnit\Framework\TestCase;
use function array_map;
use function ini_get;
use function ini_set;
use const PHP_EOL;

final class PhpMetricsTest extends TestCase
{
    public function testICanRunPhpMetricsApplicationWithSuccess(): void
    {
        $mocks = [
            'analyzer' => Phake::mock(AnalyzerInterface::class),
            'reporterHandler' => Phake::mock(ReporterHandlerInterface::class),
            'output' => Phake::mock(Output::class),
            'metrics' => Phake::mock(Metrics::class),
        ];

        $phpMetrics = new PhpMetrics(
            $mocks['analyzer'],
            $mocks['reporterHandler'],
            $mocks['output']
        );

        Phake::when($mocks['analyzer'])->__call('process', [])->thenReturn($mocks['metrics']);
        Phake::when($mocks['reporterHandler'])->__call('report', [$mocks['metrics']])->thenDoNothing();
        Phake::when($mocks['output'])->__call('writeln', [Phake::anyParameters()])->thenDoNothing();

        // Forcing configuration to something different from value set by the code.
        ini_set('xdebug.max_nesting_level', 99);

        $exitStatus = $phpMetrics->run();

        self::assertNotSame('99', ini_get('xdebug.max_nesting_level'));
        self::assertSame('3000', ini_get('xdebug.max_nesting_level'));
        self::assertSame(0, $exitStatus);
        Phake::verify($mocks['analyzer'])->__call('process', []);
        Phake::verify($mocks['reporterHandler'])->__call('report', [$mocks['metrics']]);
        Phake::verify($mocks['output'])->__call('writeln', [PHP_EOL . 'Done' . PHP_EOL]);
        array_map(Phake::verifyNoOtherInteractions(...), $mocks);
    }

    public function testICanRunPhpMetricsApplicationWithError(): void
    {
        $mocks = [
            'analyzer' => Phake::mock(AnalyzerInterface::class),
            'reporterHandler' => Phake::mock(ReporterHandlerInterface::class),
            'output' => Phake::mock(Output::class),
            'metrics' => Phake::mock(Metrics::class),
        ];

        $phpMetrics = new PhpMetrics(
            $mocks['analyzer'],
            $mocks['reporterHandler'],
            $mocks['output']
        );

        Phake::when($mocks['analyzer'])->__call('process', [])->thenReturn($mocks['metrics']);
        Phake::when($mocks['reporterHandler'])->__call('report', [$mocks['metrics']])
            ->thenThrow(new Exception('Exception'));
        Phake::when($mocks['output'])->__call('writeln', [Phake::anyParameters()])->thenDoNothing();

        // Forcing configuration to something different from value set by the code.
        ini_set('xdebug.max_nesting_level', 99);

        $exitStatus = $phpMetrics->run();

        self::assertNotSame('99', ini_get('xdebug.max_nesting_level'));
        self::assertSame('3000', ini_get('xdebug.max_nesting_level'));
        self::assertSame(1, $exitStatus);
        Phake::verify($mocks['analyzer'])->__call('process', []);
        Phake::verify($mocks['reporterHandler'])->__call('report', [$mocks['metrics']]);
        Phake::verify($mocks['output'])->__call('writeln', [PHP_EOL . '<error>Exception</error>' . PHP_EOL]);
        array_map(Phake::verifyNoOtherInteractions(...), $mocks);
    }
}
