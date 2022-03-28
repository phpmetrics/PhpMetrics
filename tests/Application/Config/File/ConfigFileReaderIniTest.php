<?php
declare(strict_types=1);

namespace Tests\Hal\Application\Config\File;

use Hal\Application\Config\Config;
use Hal\Application\Config\File\ConfigFileReaderIni;
use InvalidArgumentException;
use PHPUnit\Framework\TestCase;
use function dirname;
use function realpath;
use function restore_error_handler;
use function set_error_handler;
use function sprintf;

final class ConfigFileReaderIniTest extends TestCase
{
    /**
     * Ensure the expected exception occurs when trying to read a file that is not an Ini file.
     */
    public function testICantParseNotIniFile(): void
    {
        // This test case produce a warning we need to ignore to actually test the exception is thrown.
        set_error_handler(static function (): void {
        });

        $configFilePath = realpath(dirname(__DIR__, 3)) . '/resources/invalid_config.ini';

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage(sprintf('Cannot parse configuration file "%s".', $configFilePath));

        $config = new Config();
        $reader = new ConfigFileReaderIni($configFilePath);
        $reader->read($config);

        restore_error_handler();
    }

    /**
     * Ensure the ini file is parsed and configuration is loaded.
     */
    public function testICanParseIniFile(): void
    {
        $configFilePath = realpath(dirname(__DIR__, 3)) . '/resources/test_config.ini';

        $config = new Config();
        $reader = new ConfigFileReaderIni($configFilePath);
        $reader->read($config);

        // Expectations are inferred from ./tests/resources/test_config.ini.
        $expectedConfig = [
            'includes' => ['Controller'],
            'exclude' => 'tests,Tests',
            'report' => [
                'html' => '/tmp/report/',
                'csv' => '/tmp/report.csv',
                'json' => '/tmp/report.json',
                'violations' => '/tmp/violations.xml',
            ]
        ];

        self::assertSame($expectedConfig, $config->all());
    }
}
