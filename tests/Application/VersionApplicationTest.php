<?php
declare(strict_types=1);

namespace Tests\Hal\Application;

use Generator;
use Hal\Application\VersionApplication;
use Hal\Application\VersionInfo;
use Hal\Component\File\ReaderInterface;
use Hal\Component\Output\Output;
use Phake;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use const PHP_EOL;

final class VersionApplicationTest extends TestCase
{
    /**
     * @return Generator<string, array{string, string}>
     */
    public static function provideVersions(): Generator
    {
        $fakeSemver = <<<'TXT'
        ---
        :major: 1
        :minor: 2
        :patch: 3
        :special: ''

        TXT;
        $expected = 'PhpMetrics v1.2.3 <http://www.phpmetrics.org>' . PHP_EOL .
            'by Jean-François Lépine <https://twitter.com/Halleck45>' . PHP_EOL;
        yield 'Basic X.Y.Z' => [$fakeSemver, $expected];

        $fakeSemver = <<<'TXT'
        ---
        :major: 42
        :minor: 45
        :patch: 87
        :special: 'rc-7'

        TXT;
        $expected = 'PhpMetrics v42.45.87-rc-7 <http://www.phpmetrics.org>' . PHP_EOL .
            'by Jean-François Lépine <https://twitter.com/Halleck45>' . PHP_EOL;
        yield 'With special' => [$fakeSemver, $expected];
    }

    /**
     * @param string $fakeSemver
     * @param string $expected
     */
    #[DataProvider('provideVersions')]
    public function testICanRunVersionApplication(string $fakeSemver, string $expected): void
    {
        $fileReader = Phake::mock(ReaderInterface::class);

        Phake::when($fileReader)->__call('read', ['/test/.semver'])->thenReturn($fakeSemver);
        (new VersionInfo($fileReader))->inferVersionFromSemver('/test/.semver');
        $mockOutput = Phake::mock(Output::class);

        $app = new VersionApplication($mockOutput);
        Phake::when($mockOutput)->__call('writeln', [Phake::anyParameters()])->thenDoNothing();

        $exitStatus = $app->run();
        self::assertSame(0, $exitStatus);
        Phake::verify($mockOutput)->__call('writeln', [$expected]);
        Phake::verifyNoOtherInteractions($mockOutput);
    }
}
