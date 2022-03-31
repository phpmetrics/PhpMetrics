<?php
declare(strict_types=1);

namespace Tests\Hal\Application\Config\File;

use Generator;
use Hal\Application\Config\File\ConfigFileReaderFactory;
use Hal\Application\Config\File\ConfigFileReaderIni;
use Hal\Application\Config\File\ConfigFileReaderInterface;
use Hal\Application\Config\File\ConfigFileReaderJson;
use Hal\Application\Config\File\ConfigFileReaderYaml;
use InvalidArgumentException;
use PHPUnit\Framework\TestCase;
use function chmod;
use function dirname;
use function get_class;
use function realpath;
use function sprintf;

final class ConfigFileReaderFactoryTest extends TestCase
{
    public static function setUpBeforeClass(): void
    {
        // Update the permissions to some files to make sure they're unreadable.
        $file = realpath(dirname(__DIR__, 3)) . '/resources/test_config.no_read_perm';
        chmod($file, 0o200);
    }

    /**
     * Ensure the expected exception occurs when trying to load a configuration file that does not exist.
     */
    public function testICantUseMissingFile(): void
    {
        $configFilePath = realpath(dirname(__DIR__, 3)) . '/resources/this_is_not_an_existing_file';
        self::assertFileDoesNotExist($configFilePath);

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage(sprintf('Cannot read configuration file "%s".', $configFilePath));

        ConfigFileReaderFactory::createFromFileName($configFilePath);
    }

    /**
     * Ensure the expected exception occurs when trying to load a configuration file which is unreadable.
     */
    public function testICantUseUnreadableFile(): void
    {
        $configFilePath = realpath(dirname(__DIR__, 3)) . '/resources/test_config.no_read_perm';

        self::assertFileExists($configFilePath);
        self::assertIsNotReadable($configFilePath);

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage(sprintf('Cannot read configuration file "%s".', $configFilePath));

        ConfigFileReaderFactory::createFromFileName($configFilePath);
    }

    /**
     * Provide valid configuration files with all allowed extensions.
     *
     * @return Generator<string, array{0: string, 1: class-string<ConfigFileReaderInterface>}>
     */
    public function provideValidConfigurationFiles(): Generator
    {
        $resourcesTestDir = realpath(dirname(__DIR__, 3)) . '/resources';
        yield 'JSON file' => [$resourcesTestDir . '/test_config.json', ConfigFileReaderJson::class];
        yield 'Ini file' => [$resourcesTestDir . '/test_config.ini', ConfigFileReaderIni::class];
        yield 'Yaml file' => [$resourcesTestDir . '/test_config.yaml', ConfigFileReaderYaml::class];
        yield 'YML file' => [$resourcesTestDir . '/test_config_minimum.yml', ConfigFileReaderYaml::class];
    }

    /**
     * Ensure the valid configuration files are usable and produces the expected readers.
     *
     * @dataProvider provideValidConfigurationFiles
     * @param string $configFilePath
     * @param class-string<ConfigFileReaderInterface> $expectedReaderClassName
     */
    //#[DataProvider('provideValidConfigurationFiles')] // TODO PHPUnit 10: use attribute instead of annotation.
    public function testICanUseValidFile(string $configFilePath, string $expectedReaderClassName): void
    {
        self::assertFileExists($configFilePath);
        self::assertIsReadable($configFilePath);

        $reader = ConfigFileReaderFactory::createFromFileName($configFilePath);
        self::assertSame($expectedReaderClassName, get_class($reader));
    }

    /**
     * Ensure the expected exception occurs when trying to load a configuration file which format is not allowed.
     */
    public function testICantUseDisallowedFile(): void
    {
        $configFilePath = realpath(dirname(__DIR__, 3)) . '/resources/test_config.txt';
        self::assertFileExists($configFilePath);
        self::assertIsReadable($configFilePath);

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage(sprintf('Unsupported config file format: "%s".', $configFilePath));

        ConfigFileReaderFactory::createFromFileName($configFilePath);
    }

    public static function tearDownAfterClass(): void
    {
        // Update the permissions to reset them for updated files.
        $file = realpath(dirname(__DIR__, 3)) . '/resources/test_config.no_read_perm';
        chmod($file, 0o644);
    }
}
