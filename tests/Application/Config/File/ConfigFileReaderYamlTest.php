<?php
declare(strict_types=1);

namespace Tests\Hal\Application\Config\File;

use Generator;
use Hal\Application\Config\Config;
use Hal\Application\Config\File\ConfigFileReaderYaml;
use Hal\Component\File\ReaderInterface;
use Hal\Exception\ConfigException\ConfigFileReadingException;
use Phake;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

final class ConfigFileReaderYamlTest extends TestCase
{
    /**
     * Ensure the expected exception occurs when trying to read a file that is not a Yaml file.
     */
    public function testICantParseYamlFile(): void
    {
        $fileReader = Phake::mock(ReaderInterface::class);
        $configFilePath = '/test/config/foo_bar.yml';

        Phake::when($fileReader)->__call('readYaml', [$configFilePath])->thenReturn(false);

        $this->expectExceptionObject(ConfigFileReadingException::inYaml($configFilePath));

        $config = new Config();
        $reader = new ConfigFileReaderYaml($configFilePath, $fileReader);
        $reader->read($config);

        Phake::verify($fileReader)->__call('readYaml', [$configFilePath]);
        Phake::verifyNoOtherInteractions($fileReader);
    }

    /**
     * Provides valid Yaml files to be parsed and expected associated loaded configuration.
     *
     * @return Generator<string, array{string, array<string, mixed>}>
     */
    public static function provideYamlConfigurationFiles(): Generator
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
     * Ensure the Yaml file is parsed and configuration is loaded.
     *
     * @param array<string, mixed> $fakeConfig
     * @param array<string, mixed> $expectedConfig
     */
    #[DataProvider('provideYamlConfigurationFiles')]
    public function testICanParseYamlFile(array $fakeConfig, array $expectedConfig): void
    {
        $fileReader = Phake::mock(ReaderInterface::class);
        $configFilePath = '/test/config/foo_bar.yml';

        Phake::when($fileReader)->__call('readYaml', [$configFilePath])->thenReturn($fakeConfig);

        $config = new Config();
        $reader = new ConfigFileReaderYaml($configFilePath, $fileReader);
        $reader->read($config);
        self::assertSame($expectedConfig, $config->all());

        Phake::verify($fileReader)->__call('readYaml', [$configFilePath]);
        Phake::verifyNoOtherInteractions($fileReader);
    }
}
