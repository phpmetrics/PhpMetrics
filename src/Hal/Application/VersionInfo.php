<?php
declare(strict_types=1);

namespace Hal\Application;

use Hal\Component\File\ReaderInterface;
use function preg_match_all;
use function trim;

/**
 * Accessor for the version of the application.
 */
final class VersionInfo
{
    private static string $version;

    public function __construct(private readonly ReaderInterface $fileReader)
    {
    }

    public static function getVersion(): string
    {
        return self::$version;
    }

    public function inferVersionFromSemver(string $semverFile): void
    {
        /** @var string $semverContent The semver file exists and is readable. */
        $semverContent = $this->fileReader->read($semverFile);

        preg_match_all('#:(?:major|minor|patch|special):\s*(.*)#', $semverContent, $matches);
        [, $v] = $matches;
        /** @var array{string, string, string, string} $v As the .semver file is always on the same format. */
        $v[3] = trim($v[3], "'");
        self::$version = 'v' . $v[0] . '.' . $v[1] . '.' . $v[2] . ('' !== $v[3] ? '-' . $v[3] : '');
    }
}
