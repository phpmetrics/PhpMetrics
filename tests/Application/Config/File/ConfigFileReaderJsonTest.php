<?php
declare(strict_types=1);

namespace Tests\Hal\Application\Config\File;

use Generator;
use Hal\Application\Config\Config;
use Hal\Application\Config\File\ConfigFileReaderJson;
use Hal\Component\File\ReaderInterface;
use Hal\Exception\ConfigException\ConfigFileReadingException;
use Phake;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

final class ConfigFileReaderJsonTest extends TestCase
{
    /**
     * Ensure the expected exception occurs when trying to read a file that is unreadable, or missing.
     */
    public function testICantParseJsonFile(): void
    {
        $fileReader = Phake::mock(ReaderInterface::class);
        $configFilePath = '/test/config/foo_bar.json';

        Phake::when($fileReader)->__call('readJson', [$configFilePath])->thenReturn(false);

        $this->expectExceptionObject(ConfigFileReadingException::inJson($configFilePath));

        $config = new Config();
        $reader = new ConfigFileReaderJson($configFilePath, $fileReader);
        $reader->read($config);

        Phake::verify($fileReader)->__call('readJson', [$configFilePath]);
        Phake::verifyNoOtherInteractions($fileReader);
    }

    /**
     * Provides valid JSON files to be parsed and expected associated loaded configuration.
     *
     * @return Generator<string, array{string, array<string, mixed>}>
     */
    public static function provideJsonConfigurationFiles(): Generator
    {
        yield 'Minimum configuration' => [[], ['composer' => true]];

        $fakeConfig = [
            'includes' => ['Controller', '/src/other/files'],
            'excludes' => ['tests', 'Tests'],
            'extensions' => ['php', 'php.inc', 'php8'],
            'report' => [
                'html' => 'report/with/relative/path',
                'csv' => '/report/with/absolute/path',
            ],
            'groups' => [
                ['name' => 'Component', 'match' => '!component!i'],
                ['name' => 'Reporters', 'match' => '!Report!']
            ],
            'plugins' => [
                'junit' => ['report' => '/tmp/junit.xml']
            ],
        ];
        $expectedConfig = [
            'files' => ['/test/config/Controller', '/src/other/files'],
            'groups' => [
                ['name' => 'Component', 'match' => '!component!i'],
                ['name' => 'Reporters', 'match' => '!Report!'],
            ],
            'extensions' => 'php,php.inc,php8',
            'composer' => true,
            'exclude' => 'tests,Tests',
            'report-html' => '/test/config/report/with/relative/path',
            'report-csv' => '/report/with/absolute/path',
        ];
        yield 'Complete configuration' => [$fakeConfig, $expectedConfig];
    }

    /**
     * Ensure the JSON file is parsed and configuration is loaded.
     *
     * @param array<string, mixed> $fakeConfig
     * @param array<string, mixed> $expectedConfig
     */
    #[DataProvider('provideJsonConfigurationFiles')]
    public function testICanParseJsonFile(array $fakeConfig, array $expectedConfig): void
    {
        $fileReader = Phake::mock(ReaderInterface::class);
        $configFilePath = '/test/config/foo_bar.json';

        Phake::when($fileReader)->__call('readJson', [$configFilePath])->thenReturn($fakeConfig);

        $config = new Config();
        $reader = new ConfigFileReaderJson($configFilePath, $fileReader);
        $reader->read($config);
        self::assertSame($expectedConfig, $config->all());

        Phake::verify($fileReader)->__call('readJson', [$configFilePath]);
        Phake::verifyNoOtherInteractions($fileReader);
    }
}
