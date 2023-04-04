<?php
declare(strict_types=1);

namespace Hal\Component\File;

/**
 * Contains proxy methods to PHP functions related to file system actions, but without adding/editing/deleting files.
 */
interface SystemInterface
{
    /**
     * Returns true if the given path exists on the file system. False otherwise.
     *
     * @param string $path
     * @return bool
     */
    public function exists(string $path): bool;

    /**
     * Create the given directory if it does not exist.
     *
     * @param string $path
     * @return void
     */
    public function ensureDirectoryExists(string $path): void;

    /**
     * @param string $pattern
     * @return array<string>|false
     */
    public function glob(string $pattern): array|false;
}
