<?php
declare(strict_types=1);

namespace Tests\Hal\Application;

use Hal\Application\VersionInfo;
use PHPUnit\Framework\TestCase;
use function dirname;
use function file_get_contents;
use function preg_match_all;

final class VersionInfoTest extends TestCase
{
    public function testICanGetTheVersionOfTheApplication(): void
    {
        $semverFile = dirname(__DIR__, 2) . '/.semver';
        // Infer the version of the project regarding the .semver file.
        VersionInfo::inferVersionFromSemver($semverFile);

        /** @var string $semverContent */
        $semverContent = file_get_contents($semverFile);

        preg_match_all('#:(?:major|minor|patch|special):\s*(.*)#', $semverContent, $matches);
        [, $v] = $matches;
        $expectedVersion = 'v' . $v[0] . '.' . $v[1] . '.' . $v[2] . ('' !== $v[3] ? '-' . $v[3] : '');

        self::assertSame($expectedVersion, VersionInfo::getVersion());
    }
}
