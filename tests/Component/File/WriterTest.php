<?php
declare(strict_types=1);

namespace Tests\Hal\Component\File;

use Hal\Component\File\Writer;
use JsonException;
use PHPUnit\Framework\TestCase;
use function chmod;
use function file_get_contents;
use function mkdir;
use function touch;

final class WriterTest extends TestCase
{
    use TraitTestSystem;

    public function testWrite(): void
    {
        $file = self::getAbsoluteRandomFolderPath() . '/write.txt';
        $content = 'This is a test content.';
        (new Writer())->write($file, $content);

        self::assertSame('This is a test content.', file_get_contents($file));
    }

    public function testWriteCsv(): void
    {
        $file = self::getAbsoluteRandomFolderPath() . '/write.csv';
        $header = ['Foo', 'Bar', 'FooBar'];
        $data = [
            ['o', 'o', 'o'],
            ['o', 'x', 'o'],
            ['x', 'o', 'o'],
            ['o', 'o', 'x'],
        ];
        (new Writer())->writeCsv($file, $data, $header);

        $expected = <<<'CSV'
        Foo,Bar,FooBar
        o,o,o
        o,x,o
        x,o,o
        o,o,x
        
        CSV;
        self::assertSame($expected, file_get_contents($file));
    }

    /**
     * @throws JsonException
     */
    public function testWritePrettyJson(): void
    {
        $file = self::getAbsoluteRandomFolderPath() . '/write-pretty.json';
        $data = [
            'section' => [1, 2],
            'foo' => null,
            'true' => true,
            8
        ];
        (new Writer())->writePrettyJson($file, $data);

        $expected = <<<'JSON'
        {
            "section": [
                1,
                2
            ],
            "foo": null,
            "true": true,
            "0": 8
        }
        JSON;
        self::assertSame($expected, file_get_contents($file));
    }

    public function testIsWritable(): void
    {
        $file = self::getAbsoluteRandomFolderPath() . '/has-permission';
        touch($file);
        chmod($file, 0o777); // Permission to write.
        self::assertTrue((new Writer())->isWritable($file));
    }

    public function testIsNotWritable(): void
    {
        $file = self::getAbsoluteRandomFolderPath() . '/has-no-permission';
        touch($file);
        chmod($file, 0o555); // Forbidden to write.
        self::assertFalse((new Writer())->isWritable($file));
    }

    public function testCopy(): void
    {
        $file = self::getAbsoluteRandomFolderPath() . '/src';
        touch($file);
        $dest = self::getAbsoluteRandomFolderPath() . '/dest';
        (new Writer())->copy($file, $dest);

        self::assertFileExists($dest);
    }

    public function testRecursiveCopy(): void
    {
        $src = self::getAbsoluteRandomFolderPath() . '/recursive-copy-src';
        $dest = self::getAbsoluteRandomFolderPath() . '/recursive-copy-dest';

        // Prepares the tree to copy from src to dest.
        mkdir($src);
        mkdir($src . '/A');
        mkdir($src . '/A/a');
        touch($src . '/A/a/.hiddenFile');
        touch($src . '/A/a/Aa.txt');
        mkdir($src . '/A/b');
        touch($src . '/B');

        (new Writer())->recursiveCopy($src, $dest);

        // Ensure the new tree has been copied recursively.
        self::assertDirectoryExists($dest);
        self::assertDirectoryExists($dest . '/A');
        self::assertDirectoryExists($dest . '/A/a');
        self::assertFileExists($dest . '/A/a/.hiddenFile');
        self::assertFileExists($dest . '/A/a/Aa.txt');
        self::assertDirectoryExists($dest . '/A/b');
        self::assertFileExists($dest . '/B');
    }

    public static function tearDownAfterClass(): void
    {
        self::rm(self::getAbsoluteRandomFolderPath());
        parent::tearDownAfterClass();
    }
}
