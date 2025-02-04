<?php
declare(strict_types=1);

namespace Tests\Hal\Component\File;

use Generator;
use Hal\Component\File\System;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use function chmod;
use function mkdir;
use function touch;

final class SystemTest extends TestCase
{
    use TraitTestSystem;

    /**
     * @return Generator<string, array{string, bool}>
     */
    public static function providerExists(): Generator
    {
        $path = self::getAbsoluteRandomFolderPath() . '/' . __FUNCTION__;
        mkdir($path, recursive: true);

        yield 'not existing file' => [$path . '/not_existing_file.txt', false];

        $fileName = '/existing_file.txt';
        touch($path . $fileName);
        yield 'existing file' => [$path . $fileName, true];

        $subfolder = '/subfolder';
        mkdir($path . $subfolder);
        touch($path . $subfolder . $fileName);
        chmod($path . $subfolder, 0o000);
        yield 'existing file in non-readable folder' => [$path . $subfolder . $fileName, false];
    }

    #[DataProvider('providerExists')]
    public function testExists(string $filename, bool $expectedResult): void
    {
        $system = new System();
        self::assertSame($expectedResult, $system->exists($filename));
    }

    /**
     * @return Generator<string, array{string, bool}>
     */
    public static function providerEnsureDirectoryExists(): Generator
    {
        $path = self::getAbsoluteRandomFolderPath() . '/' . __FUNCTION__;
        mkdir($path, recursive: true);

        yield 'not existing folder' => [$path . '/not_existing_folder', false];

        yield 'not existing sub-folder' => [$path . '/not_existing/sub/folder', false];

        $folderPath = '/existing_folder';
        $folderPath = $path . $folderPath;
        mkdir($folderPath, recursive: true);
        $absoluteFolderPath = $path . $folderPath;
        yield 'existing folder' => [$absoluteFolderPath, true];

        $folderPath = '/existing_sub/folder';
        $folderPath = $path . $folderPath;
        mkdir($folderPath, recursive: true);
        yield 'existing sub-folder' => [$folderPath, true];
    }

    #[DataProvider('providerEnsureDirectoryExists')]
    public function testEnsureDirectoryExists(string $directoryPath): void
    {
        $system = new System();
        $system->ensureDirectoryExists($directoryPath);
        self::assertDirectoryExists($directoryPath);
    }

    /**
     * @return Generator<string, array{string, list<string>|false}>
     */
    public static function providerGlob(): Generator
    {
        $path = self::getAbsoluteRandomFolderPath() . '/' . __FUNCTION__;
        mkdir($path, recursive: true);

        yield 'not existing folder' => [$path . '/glob_not_existing_folder/*', []];
        yield 'not existing sub-folder' => [$path . '/glob_not_existing/sub/folder', []];

        $emptyFolderPath = $path . '/glob_empty_folder';
        mkdir($emptyFolderPath, recursive: true);
        yield 'existing empty folder' => [$emptyFolderPath . '/*', []];

        $nonEmptyFolderPath = $path . '/glob_non_empty_folder';
        mkdir($nonEmptyFolderPath, recursive: true);
        touch($nonEmptyFolderPath . '/my_file.txt');
        yield 'existing non-empty folder' => [$nonEmptyFolderPath . '/*', [$nonEmptyFolderPath . '/my_file.txt']];
    }

    #[DataProvider('providerGlob')]
    public function testGlob(string $pattern, array|false $expectedContent): void
    {
        $system = new System();
        self::assertSame($expectedContent, $system->glob($pattern));
    }

    public static function tearDownAfterClass(): void
    {
        $path = self::getAbsoluteRandomFolderPath();
        // Reset permissions on specific folder to be able to remove it.
        chmod($path . '/providerExists/subfolder', 0o777);

        self::rm($path);
        parent::tearDownAfterClass();
    }
}
