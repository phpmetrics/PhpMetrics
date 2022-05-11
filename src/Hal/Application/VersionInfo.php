<?php
declare(strict_types=1);

namespace Hal\Application;

/**
 * Accessor for the version of the application.
 */
final class VersionInfo
{
    public static function getVersion(): string
    {
        // TODO: write a parser that will read the .semver file.
        //$semverFile = PROJECT_ROOT . '/.semver';
        return 'v3.0.0';
    }
}
