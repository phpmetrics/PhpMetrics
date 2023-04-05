<?php
declare(strict_types=1);

namespace Tests\Hal\Component\File;

use FilesystemIterator;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;
use function basename;
use function chmod;
use function mkdir;
use function random_int;
use function rmdir;
use function str_replace;
use function unlink;
use const PHP_INT_MAX;

trait TraitTestSystem
{
    protected static string $absoluteRandomFolderPath;

    private static function initializeRandomFolderPaths(): void
    {
        $dedicatedFolder = basename(str_replace('\\', '/', static::class));
        /** @noinspection PhpUnhandledExceptionInspection Random seed is not expected to fail. */
        self::$absoluteRandomFolderPath = '/tmp/' . $dedicatedFolder . '/' . random_int(0, PHP_INT_MAX);
        mkdir(self::$absoluteRandomFolderPath, recursive: true);
    }


    protected static function getAbsoluteRandomFolderPath(): string
    {
        if (!isset(self::$absoluteRandomFolderPath)) {
            self::initializeRandomFolderPaths();
        }
        return self::$absoluteRandomFolderPath;
    }

    /**
     * Remove an item and its sub-items, if it's a folder.
     *
     * @param string $item Filepath to remove
     */
    private static function rm(string $item): void
    {
        $fileIterator = new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator($item, FilesystemIterator::SKIP_DOTS),
            RecursiveIteratorIterator::CHILD_FIRST
        );
        foreach ($fileIterator as $file) {
            /** @var RecursiveDirectoryIterator $file */

            chmod($file->getRealPath(), 0o777);
            if ($file->isDir()) {
                rmdir($file->getRealPath());
            } else {
                unlink($file->getRealPath());
            }
        }
        rmdir($item);
    }
}
