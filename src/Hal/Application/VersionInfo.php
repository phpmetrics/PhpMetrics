<?php
declare(strict_types=1);

namespace Hal\Application;

use function file_get_contents;
use function preg_match_all;
use function trim;

/**
 * Accessor for the version of the application.
 */
final class VersionInfo
{
    private static string $version;

    public static function getVersion(): string
    {
        return self::$version;
    }

    public static function inferVersionFromSemver(string $semverFile): void
    {
        /** @var string $semverContent */
        $semverContent = file_get_contents($semverFile);

        preg_match_all('#:(?:major|minor|patch|special):\s*(.*)#', $semverContent, $matches);
        [, $v] = $matches;
        $v[3] = trim($v[3], "'");
        self::$version = 'v' . $v[0] . '.' . $v[1] . '.' . $v[2] . ('' !== $v[3] ? '-' . $v[3] : '');
    }
}
