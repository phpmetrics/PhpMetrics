<?php
declare(strict_types=1);

namespace Tests\Hal\Application;

use Hal\Application\Analyzer;
use Hal\Application\Workflow\WorkflowHandlerInterface;
use Hal\Component\File\FinderInterface;
use Hal\Metric\Metrics;
use Hal\Violation\ViolationParserInterface;
use Phake;
use PHPUnit\Framework\TestCase;
use function array_map;

final class AnalyzerTest extends TestCase
{
    public function testTheAnalyzerProcess(): void
    {
        $mocks = [
            'finder' => Phake::mock(FinderInterface::class),
            'workflowHandler' => Phake::mock(WorkflowHandlerInterface::class),
            'violationParser' => Phake::mock(ViolationParserInterface::class),
            'metrics' => Phake::mock(Metrics::class),
        ];
        $pathsList = ['./', 'some_file.txt'];
        $files = [];

        $analyzer = new Analyzer(
            $pathsList,
            $mocks['finder'],
            $mocks['workflowHandler'],
            $mocks['violationParser']
        );

        Phake::when($mocks['finder'])->__call('fetch', [$pathsList])->thenReturn($files);
        Phake::when($mocks['workflowHandler'])->__call('execute', [$files])->thenReturn($mocks['metrics']);
        Phake::when($mocks['violationParser'])->__call('apply', [$mocks['metrics']])->thenDoNothing();

        $actualMetrics = $analyzer->process();

        self::assertSame($mocks['metrics'], $actualMetrics);
        Phake::verify($mocks['finder'])->__call('fetch', [$pathsList]);
        Phake::verify($mocks['workflowHandler'])->__call('execute', [$files]);
        Phake::verify($mocks['violationParser'])->__call('apply', [$mocks['metrics']]);
        array_map(Phake::verifyNoOtherInteractions(...), $mocks);
    }
}
