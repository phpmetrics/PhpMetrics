<?php
declare(strict_types=1);

namespace Tests\Hal\Application;

use Hal\Application\Config\Validator;
use Hal\Application\HelpApplication;
use Hal\Component\Output\Output;
use Phake;
use PHPUnit\Framework\TestCase;

final class HelpApplicationTest extends TestCase
{
    public function testICanRunHelpApplication(): void
    {
        $mockOutput = Phake::mock(Output::class);

        $app = new HelpApplication($mockOutput);
        Phake::when($mockOutput)->__call('writeln', [Phake::anyParameters()])->thenDoNothing();

        $exitStatus = $app->run();

        self::assertSame(0, $exitStatus);
        Phake::verify($mockOutput)->__call('writeln', [Validator::help()]);
        Phake::verifyNoOtherInteractions($mockOutput);
    }
}
