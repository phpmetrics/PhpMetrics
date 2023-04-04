<?php
declare(strict_types=1);

namespace Tests\Hal\Application;

use Generator;
use Hal\Application\VersionInfo;
use Hal\Component\File\ReaderInterface;
use Phake;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

final class VersionInfoTest extends TestCase
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
        $expected = 'v1.2.3';
        yield 'Basic X.Y.Z' => [$fakeSemver, $expected];

        $fakeSemver = <<<'TXT'
        ---
        :major: 42
        :minor: 45
        :patch: 87
        :special: 'rc-7'

        TXT;
        $expected = 'v42.45.87-rc-7';
        yield 'With special' => [$fakeSemver, $expected];
    }

    /**
     * @param string $fakeSemver
     * @param string $expected
     */
    #[DataProvider('provideVersions')]
    public function testICanGetTheVersionOfTheApplication(string $fakeSemver, string $expected): void
    {
        $fileReader = Phake::mock(ReaderInterface::class);

        Phake::when($fileReader)->__call('read', ['/test/.semver'])->thenReturn($fakeSemver);
        (new VersionInfo($fileReader))->inferVersionFromSemver('/test/.semver');
        self::assertSame($expected, VersionInfo::getVersion());
    }
}
