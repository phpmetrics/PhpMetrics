<?php
declare(strict_types=1);

namespace Tests\Hal\Application;

use Hal\Application\Config\Config;
use Hal\Application\ErrorApplication;
use Hal\Component\Output\Output;
use Phake;
use PHPUnit\Framework\TestCase;

final class ErrorApplicationTest extends TestCase
{
    public function testICanRunErrorApplication(): void
    {
        $config = new Config();
        $config->set('config-error', 'TestValue');
        $mockOutput = Phake::mock(Output::class);

        $app = new ErrorApplication($config, $mockOutput);
        Phake::when($mockOutput)->__call('writeln', [Phake::anyParameters()])->thenDoNothing();

        $exitStatus = $app->run();

        self::assertSame(1, $exitStatus);
        Phake::verify($mockOutput)->__call('writeln', ["\n<error>TestValue</error>\n"]);
        Phake::verifyNoOtherInteractions($mockOutput);
    }
}
