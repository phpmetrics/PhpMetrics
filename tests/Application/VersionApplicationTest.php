<?php
declare(strict_types=1);

namespace Tests\Hal\Application;

use Hal\Application\VersionApplication;
use Hal\Component\Output\Output;
use Phake;
use PHPUnit\Framework\TestCase;
use function getVersion;
use function sprintf;
use const PHP_EOL;

final class VersionApplicationTest extends TestCase
{
    // TODO: Update to not use global function getVersion anymore.
    public function testICanRunVersionApplication(): void
    {
        $mockOutput = Phake::mock(Output::class);

        $app = new VersionApplication($mockOutput);
        Phake::when($mockOutput)->__call('writeln', [Phake::anyParameters()])->thenDoNothing();

        $exitStatus = $app->run();
        $expectedText = 'PhpMetrics %s <http://www.phpmetrics.org>' . PHP_EOL .
            'by Jean-François Lépine <https://twitter.com/Halleck45>' . PHP_EOL;

        self::assertSame(0, $exitStatus);
        Phake::verify($mockOutput)->__call('writeln', [sprintf($expectedText, getVersion())]);
        Phake::verifyNoOtherInteractions($mockOutput);
    }
}
