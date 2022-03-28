<?php
declare(strict_types=1);

namespace Tests\Hal\Application\Workflow\Task;

use Error;
use Hal\Application\Workflow\Task\PrepareParserTask;
use Hal\Component\Output\Output;
use Phake;
use PhpParser\Node;
use PhpParser\NodeTraverserInterface;
use PhpParser\Parser;
use PHPUnit\Framework\TestCase;
use function array_map;
use function array_values;
use function dirname;
use function file_get_contents;
use function realpath;

final class PrepareParserTaskTest extends TestCase
{
    public function testICanTraverseSomeFilesWithParser(): void
    {
        $resourcesTestDir = realpath(dirname(__DIR__, 3)) . '/resources';
        $files = [
            'is_ok' => $resourcesTestDir . '/parser_ok.php',
            'will_throw_error' => $resourcesTestDir . '/parser_ko.php',
        ];
        $fileContents = array_map(file_get_contents(...), $files);

        $mocks = [
            'parser' => Phake::mock(Parser::class),
            'nodeTraverser' => Phake::mock(NodeTraverserInterface::class),
            'output' => Phake::mock(Output::class),
        ];
        $mockNodes = [Phake::mock(Node::class), Phake::mock(Node::class)];

        Phake::when($mocks['parser'])->__call('parse', [$fileContents['is_ok']])->thenReturn($mockNodes);
        Phake::when($mocks['parser'])->__call('parse', [$fileContents['will_throw_error']])
            ->thenThrow(new Error('Error'));
        Phake::when($mocks['nodeTraverser'])->__call('traverse', [$mockNodes])->thenReturn($mockNodes);
        Phake::when($mocks['output'])->__call('writeln', [Phake::anyParameters()])->thenDoNothing();

        $task = new PrepareParserTask(
            $mocks['parser'],
            $mocks['nodeTraverser'],
            $mocks['output']
        );

        $task->process(array_values($files));

        Phake::verify($mocks['parser'])->__call('parse', [$fileContents['is_ok']]);
        Phake::verify($mocks['parser'])->__call('parse', [$fileContents['will_throw_error']]);
        Phake::verify($mocks['nodeTraverser'])->__call('traverse', [$mockNodes]);
        $expectedOutput = '<error>Cannot parse ' . $resourcesTestDir . '/parser_ko.php</error>';
        Phake::verify($mocks['output'])->__call('writeln', [$expectedOutput]);
        array_map(Phake::verifyNoOtherInteractions(...), $mocks);
        array_map(Phake::verifyNoOtherInteractions(...), $mockNodes);
    }
}
