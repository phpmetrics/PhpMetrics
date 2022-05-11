<?php
declare(strict_types=1);

namespace Tests\Hal\Application\Config\File;

use Generator;
use Hal\Application\Config\Config;
use Hal\Application\Config\File\ConfigFileReaderJson;
use Hal\Exception\ConfigException\ConfigFileReadingException;
use JsonException;
use PHPUnit\Framework\TestCase;
use function chmod;
use function dirname;
use function realpath;
use function restore_error_handler;
use function set_error_handler;

final class ConfigFileReaderJsonTest extends TestCase
{
    public static function setUpBeforeClass(): void
    {
        // Update the permissions to some files to make sure they're unreadable.
        $file = realpath(dirname(__DIR__, 3)) . '/resources/test_config.no_read_perm';
        chmod($file, 0o200);
    }

    /**
     * Ensure the expected exception occurs when trying to read a file that is unreadable, or missing.
     *
     * @throws JsonException Ignored in this case as we do not even reach the json_decode call.
     */
    public function testICantParseUnreadableFile(): void
    {
        // This test case produce a warning we need to ignore to actually test the exception is thrown.
        set_error_handler(static function (): void {
        });

        $configFilePath = realpath(dirname(__DIR__, 3)) . '/resources/test_config.no_read_perm';

        $this->expectExceptionObject(ConfigFileReadingException::inJson($configFilePath));

        $config = new Config();
        $reader = new ConfigFileReaderJson($configFilePath);
        $reader->read($config);

        restore_error_handler();
    }

    /**
     * Ensure the expected exception occurs when trying to read a file that is not a JSON file.
     *
     * @throws JsonException That is exactly the expected exception in this case.
     */
    public function testICantParseNotJsonFile(): void
    {
        $configFilePath = realpath(dirname(__DIR__, 3)) . '/resources/test_config.ini';

        $this->expectException(JsonException::class);

        $config = new Config();
        $reader = new ConfigFileReaderJson($configFilePath);
        $reader->read($config);
    }

    /**
     * Provides valid JSON files to be parsed and expected associated loaded configuration.
     *
     * @return Generator<string, array{0: string, 1: array<string, mixed>}>
     */
    public function provideJsonConfigurationFiles(): Generator
    {
        $resourcesTestDir = realpath(dirname(__DIR__, 3)) . '/resources';
        yield 'Minimum configuration' => [$resourcesTestDir . '/test_config_minimum.json', ['composer' => true]];

        // Expectations are inferred from associated configuration file.
        $expectedConfig = [
            'files' => [$resourcesTestDir . '/Controller', '/src/other/files'],
            'groups' => [
                ['name' => 'Component', 'match' => '!component!i'],
                ['name' => 'Reporters', 'match' => '!Report!'],
            ],
            'extensions' => 'php,php.inc,php8',
            'composer' => true,
            'exclude' => 'tests,Tests',
            'report-html' => $resourcesTestDir . '/report/with/relative/path',
            'report-csv' => '/report/with/absolute/path',
        ];
        yield 'Complete configuration' => [$resourcesTestDir . '/test_config.json', $expectedConfig];
    }

    /**
     * Ensure the JSON file is parsed and configuration is loaded.
     *
     * @dataProvider provideJsonConfigurationFiles
     * @param string $configFilePath
     * @param array<string, mixed> $expectedConfig
     *
     * @throws JsonException Files is the provider are not expecting to be invalid JSON, therefore, this exception
     *     should never be thrown in this case.
     */
    //#[DataProvider('provideJsonConfigurationFiles')] // TODO PHPUnit 10: use attribute instead of annotation.
    public function testICanParseJsonFile(string $configFilePath, array $expectedConfig): void
    {
        $config = new Config();
        $reader = new ConfigFileReaderJson($configFilePath);
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
