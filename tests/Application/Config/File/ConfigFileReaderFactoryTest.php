<?php
declare(strict_types=1);

namespace Tests\Hal\Application\Config\File;

use Generator;
use Hal\Application\Config\File\ConfigFileReaderFactory;
use Hal\Application\Config\File\ConfigFileReaderIni;
use Hal\Application\Config\File\ConfigFileReaderInterface;
use Hal\Application\Config\File\ConfigFileReaderJson;
use Hal\Application\Config\File\ConfigFileReaderYaml;
use Hal\Component\File\ReaderInterface;
use InvalidArgumentException;
use Phake;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use function get_class;
use function sprintf;

final class ConfigFileReaderFactoryTest extends TestCase
{
    /**
     * Ensure the expected exception occurs when trying to load a configuration file that does not exist.
     */
    public function testICantUseMissingFile(): void
    {
        $fileReader = Phake::mock(ReaderInterface::class);
        $file = '/test/config/factory/missing.txt';

        Phake::when($fileReader)->__call('exists', [$file])->thenReturn(false);

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage(sprintf('Cannot read configuration file "%s".', $file));

        (new ConfigFileReaderFactory($fileReader))->createFromFileName($file);

        Phake::verify($fileReader)->__call('exists', [$file]);
        Phake::verifyNoOtherInteractions($fileReader);
    }

    /**
     * Ensure the expected exception occurs when trying to load a configuration file which is unreadable.
     */
    public function testICantUseUnreadableFile(): void
    {
        $fileReader = Phake::mock(ReaderInterface::class);
        $file = '/test/config/factory/unreadable.txt';

        Phake::when($fileReader)->__call('exists', [$file])->thenReturn(true);
        Phake::when($fileReader)->__call('isReadable', [$file])->thenReturn(false);

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage(sprintf('Cannot read configuration file "%s".', $file));

        (new ConfigFileReaderFactory($fileReader))->createFromFileName($file);

        Phake::verify($fileReader)->__call('exists', [$file]);
        Phake::verify($fileReader)->__call('isReadable', [$file]);
        Phake::verifyNoOtherInteractions($fileReader);
    }

    /**
     * Provide valid configuration files with all allowed extensions.
     *
     * @return Generator<string, array{string, class-string<ConfigFileReaderInterface>}>
     */
    public static function provideValidConfigurationFiles(): Generator
    {
        yield 'JSON file' => ['/test/config/test_config.json', ConfigFileReaderJson::class];
        yield 'Ini file' => ['/test/config/test_config.ini', ConfigFileReaderIni::class];
        yield 'Yaml file' => ['/test/config/test_config.yaml', ConfigFileReaderYaml::class];
        yield 'YML file' => ['/test/config/test_config_minimum.yml', ConfigFileReaderYaml::class];
    }

    /**
     * Ensure the valid configuration files are usable and produces the expected readers.
     *
     * @param string $file
     * @param class-string<ConfigFileReaderInterface> $expectedReaderClassName
     */
    #[DataProvider('provideValidConfigurationFiles')]
    public function testICanUseValidFile(string $file, string $expectedReaderClassName): void
    {
        $fileReader = Phake::mock(ReaderInterface::class);
        Phake::when($fileReader)->__call('exists', [$file])->thenReturn(true);
        Phake::when($fileReader)->__call('isReadable', [$file])->thenReturn(true);

        $reader = (new ConfigFileReaderFactory($fileReader))->createFromFileName($file);
        self::assertSame($expectedReaderClassName, get_class($reader));

        Phake::verify($fileReader)->__call('exists', [$file]);
        Phake::verify($fileReader)->__call('isReadable', [$file]);
        Phake::verifyNoOtherInteractions($fileReader);
    }

    /**
     * Ensure the expected exception occurs when trying to load a configuration file which format is not allowed.
     */
    public function testICantUseDisallowedFile(): void
    {
        $fileReader = Phake::mock(ReaderInterface::class);
        $file = '/test/config/factory/unsupported.txt';

        Phake::when($fileReader)->__call('exists', [$file])->thenReturn(true);
        Phake::when($fileReader)->__call('isReadable', [$file])->thenReturn(true);

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage(sprintf('Unsupported config file format: "%s".', $file));

        (new ConfigFileReaderFactory($fileReader))->createFromFileName($file);
    }
}
