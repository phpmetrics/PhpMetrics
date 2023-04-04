<?php
declare(strict_types=1);

namespace Tests\Hal\Application\Workflow\Task;

use Error;
use Hal\Application\Workflow\Task\PrepareParserTask;
use Hal\Component\File\ReaderInterface;
use Hal\Component\Output\Output;
use Phake;
use PhpParser\Node;
use PhpParser\NodeTraverserInterface;
use PhpParser\Parser;
use PHPUnit\Framework\TestCase;
use function array_map;

final class PrepareParserTaskTest extends TestCase
{
    public function testICanTraverseSomeFilesWithParser(): void
    {
        $files = [
            '/test/parser/ok.php',
            '/test/parser/ko.php'
        ];
        $fileContents = [
            '/test/parser/ok.php' => <<<'PHP'
            <?php
            // This file does not need to contain anything. Only its name is important.
            // Name: parser_ok.php

            PHP,
            '/test/parser/ko.php' => <<<'PHP'
            <?php
            // This file does not need to contain anything. Only its name is important.
            // Name: parser_ko.php

            PHP,
        ];

        $mocks = [
            'parser' => Phake::mock(Parser::class),
            'nodeTraverser' => Phake::mock(NodeTraverserInterface::class),
            'output' => Phake::mock(Output::class),
            'fileReader' => Phake::mock(ReaderInterface::class),
        ];
        $mockNodes = [Phake::mock(Node::class), Phake::mock(Node::class)];

        Phake::when($mocks['parser'])->__call('parse', [$fileContents['/test/parser/ok.php']])->thenReturn($mockNodes);
        Phake::when($mocks['parser'])->__call('parse', [$fileContents['/test/parser/ko.php']])
            ->thenThrow(new Error('Error'));
        Phake::when($mocks['nodeTraverser'])->__call('traverse', [$mockNodes])->thenReturn($mockNodes);
        Phake::when($mocks['output'])->__call('writeln', [Phake::anyParameters()])->thenDoNothing();
        foreach ($files as $file) {
            Phake::when($mocks['fileReader'])->__call('read', [$file])->thenReturn($fileContents[$file]);
        }

        $task = new PrepareParserTask(
            $mocks['parser'],
            $mocks['nodeTraverser'],
            $mocks['output'],
            $mocks['fileReader'],
        );

        $task->process($files);

        foreach ($files as $file) {
            Phake::verify($mocks['parser'])->__call('parse', [$fileContents[$file]]);
        }
        Phake::verify($mocks['nodeTraverser'])->__call('traverse', [$mockNodes]);
        $expectedOutput = '<error>Cannot parse /test/parser/ko.php</error>';
        Phake::verify($mocks['output'])->__call('writeln', [$expectedOutput]);
        foreach ($files as $file) {
            Phake::verify($mocks['fileReader'])->__call('read', [$file]);
        }
        array_map(Phake::verifyNoOtherInteractions(...), $mocks);
        array_map(Phake::verifyNoOtherInteractions(...), $mockNodes);
    }
}
