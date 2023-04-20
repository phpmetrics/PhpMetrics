<?php
declare(strict_types=1);

namespace Tests\Hal\Component\File;

use Hal\Component\File\Reader;
use JsonException;
use PHPUnit\Framework\Attributes\IgnoreMethodForCodeCoverage;
use PHPUnit\Framework\TestCase;
use function chmod;
use function file_put_contents;
use function touch;

// Cannot be tested as file_get_contents is calling a URI, and we cannot fake its results.
#[IgnoreMethodForCodeCoverage(Reader::class, 'httpReadJson')]
final class ReaderTest extends TestCase
{
    use TraitTestSystem;

    public function testICanReadAFile(): void
    {
        $file = self::getAbsoluteRandomFolderPath() . '/read.txt';
        file_put_contents($file, 'This is a test content.');

        self::assertSame('This is a test content.', (new Reader())->read($file));
    }

    public function testICanReadIniFile(): void
    {
        $file = self::getAbsoluteRandomFolderPath() . '/read.ini';
        $content = <<<'INI'
        ; This is a comment 
        foo="FOO"
        bar="BAR"
        [section]
        foo="section_foo"
        [another_section]
        bar="section_bar"
        INI;
        file_put_contents($file, $content);

        $expected = [
            'foo' => 'FOO',
            'bar' => 'BAR',
            'section' => [
                'foo' => 'section_foo',
            ],
            'another_section' => [
                'bar' => 'section_bar',
            ],
        ];

        self::assertSame($expected, (new Reader())->readIni($file));
    }

    /**
     * @throws JsonException
     */
    public function testICanReadJsonFile(): void
    {
        $file = self::getAbsoluteRandomFolderPath() . '/read.json';
        $content = <<<'JSON'
        {
          "foo": "FOO",
          "bar": 42
        }
        JSON;
        file_put_contents($file, $content);

        $expected = [
            'foo' => 'FOO',
            'bar' => 42
        ];

        self::assertSame($expected, (new Reader())->readJson($file));
    }

    /**
     * @throws JsonException
     */
    public function testICannotReadUnreadableJsonFile(): void
    {
        $file = self::getAbsoluteRandomFolderPath() . '/no-read.json';
        $content = <<<'JSON'
        {
          "foo": "FOO",
          "bar": 42
        }
        JSON;
        file_put_contents($file, $content);
        chmod($file, 0o200);

        self::assertFalse((new Reader())->readJson($file));
    }

    public function testICanReadYamlFile(): void
    {
        $file = self::getAbsoluteRandomFolderPath() . '/read.yaml';
        $content = <<<'YAML'
        foo: "foo"
        section:
          bar: 42
        with_array:
          - "a"
          - "c"
          - "e"
        YAML;
        file_put_contents($file, $content);

        $expected = [
            'foo' => 'foo',
            'section' => [
                'bar' => 42,
            ],
            'with_array' => ['a', 'c', 'e'],
        ];

        self::assertSame($expected, (new Reader())->readYaml($file));
    }

    public function testEmptyIsReturnedIfYamlIsNull(): void
    {
        $file = self::getAbsoluteRandomFolderPath() . '/null.yaml';
        $content = <<<'YAML'
        !!null
        YAML;
        file_put_contents($file, $content);
        self::assertSame([], (new Reader())->readYaml($file));
    }

    public function testFileIsReadable(): void
    {
        $file = self::getAbsoluteRandomFolderPath() . '/readable';
        touch($file);
        self::assertTrue((new Reader())->isReadable($file));
    }

    public function testFileIsNotReadable(): void
    {
        $file = self::getAbsoluteRandomFolderPath() . '/not-readable';
        touch($file);
        chmod($file, 0o200);
        self::assertFalse((new Reader())->isReadable($file));
    }

    public static function tearDownAfterClass(): void
    {
        self::rm(self::getAbsoluteRandomFolderPath());
        parent::tearDownAfterClass();
    }
}
