<?php
declare(strict_types=1);

namespace Tests\Hal\Application\Config\File;

use Generator;
use Hal\Application\Config\Config;
use Hal\Application\Config\File\ConfigFileReaderYaml;
use Hal\Exception\ConfigException\ConfigFileReadingException;
use PHPUnit\Framework\TestCase;
use function chmod;
use function dirname;
use function realpath;
use function restore_error_handler;
use function set_error_handler;

final class ConfigFileReaderYamlTest extends TestCase
{
    public static function setUpBeforeClass(): void
    {
        // Update the permissions to some files to make sure they're unreadable.
        $file = realpath(dirname(__DIR__, 3)) . '/resources/test_config.no_read_perm';
        chmod($file, 0o200);
    }

    /**
     * Ensure the expected exception occurs when trying to read a file that is unreadable, or missing.
     */
    public function testICantParseUnreadableFile(): void
    {
        // This test case produce a warning we need to ignore to actually test the exception is thrown.
        set_error_handler(static function (): void {
        });

        $configFilePath = realpath(dirname(__DIR__, 3)) . '/resources/test_config.no_read_perm';

        $this->expectExceptionObject(ConfigFileReadingException::inYaml($configFilePath));

        $config = new Config();
        $reader = new ConfigFileReaderYaml($configFilePath);
        $reader->read($config);

        restore_error_handler();
    }

    /**
     * Ensure the expected exception occurs when trying to read a file that is not a Yaml file.
     */
    public function testICantParseNotYamlFile(): void
    {
        $configFilePath = realpath(dirname(__DIR__, 3)) . '/resources/test_config.ini';

        $this->expectExceptionObject(ConfigFileReadingException::inYaml($configFilePath));

        $config = new Config();
        $reader = new ConfigFileReaderYaml($configFilePath);
        $reader->read($config);
    }

    /**
     * Provides valid Yaml files to be parsed and expected associated loaded configuration.
     *
     * @return Generator<string, array{0: string, 1: array<string, mixed>}>
     */
    public function provideYamlConfigurationFiles(): Generator
    {
        $resourcesTestDir = realpath(dirname(__DIR__, 3)) . '/resources';
        yield 'Minimum configuration' => [$resourcesTestDir . '/test_config_minimum.yml', ['composer' => true]];

        // Expectations are inferred from associated configuration file.
        $expectedConfig = [
            'files' => [$resourcesTestDir . '/Controller', '/src/other/files'],
            'groups' => [
                ['name' => 'Component', 'match' => '!component!i'],
                ['name' => 'Reporters', 'match' => '!Report!'],
            ],
            'extensions' => 'php,php.inc,php8',
            'composer' => false,
            'exclude' => 'tests,Tests',
            'junit' => '/tmp/junit.xml',
            'report-html' => $resourcesTestDir . '/report/with/relative/path',
            'report-csv' => '/report/with/absolute/path',
        ];
        yield 'Complete configuration' => [$resourcesTestDir . '/test_config.yaml', $expectedConfig];
    }

    /**
     * Ensure the Yaml file is parsed and configuration is loaded.
     *
     * @dataProvider provideYamlConfigurationFiles
     * @param string $configFilePath
     * @param array<string, mixed> $expectedConfig
     */
    //#[DataProvider('provideYamlConfigurationFiles')] // TODO PHPUnit 10: use attribute instead of annotation.
    public function testICanParseYamlFile(string $configFilePath, array $expectedConfig): void
    {
        $config = new Config();
        $reader = new ConfigFileReaderYaml($configFilePath);
        $reader->read($config);
        self::assertSame($expectedConfig, $config->all());
    }

    public static function tearDownAfterClass(): void
    {
        // Update the permissions to reset them for updated files.
        $file = realpath(dirname(__DIR__, 3)) . '/resources/test_config.no_read_perm';
        chmod($file, 0o644);
    }
}
