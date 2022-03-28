<?php
declare(strict_types=1);

namespace Tests\Hal\Application\Workflow;

use Hal\Application\Workflow\Task\WorkflowTaskInterface;
use Hal\Application\Workflow\WorkflowHandler;
use Hal\Component\Output\Output;
use Hal\Metric\Metrics;
use Phake;
use PHPUnit\Framework\TestCase;
use function array_map;

final class WorkflowHandlerTest extends TestCase
{
    public function testTheWorkflowExecution(): void
    {
        $mocks = [
            'metrics' => Phake::mock(Metrics::class),
            'parser' => Phake::mock(WorkflowTaskInterface::class),
            'analyze' => Phake::mock(WorkflowTaskInterface::class),
            'output' => Phake::mock(Output::class),
        ];

        $files = [];

        $workflowHandler = new WorkflowHandler(
            $mocks['metrics'],
            $mocks['parser'],
            $mocks['analyze'],
            $mocks['output']
        );

        Phake::when($mocks['output'])->__call('writeln', [Phake::anyParameters()])->thenDoNothing();
        Phake::when($mocks['parser'])->__call('process', [$files])->thenDoNothing();
        Phake::when($mocks['analyze'])->__call('process', [$files])->thenDoNothing();

        $actualMetrics = $workflowHandler->execute($files);

        self::assertSame($mocks['metrics'], $actualMetrics);
        Phake::verify($mocks['output'])->__call('writeln', ['Parsing all files...']);
        Phake::verify($mocks['parser'])->__call('process', [$files]);
        Phake::verify($mocks['output'])->__call('writeln', ['Executing system analyzes...']);
        Phake::verify($mocks['analyze'])->__call('process', [$files]);
        array_map(Phake::verifyNoOtherInteractions(...), $mocks);
    }
}
