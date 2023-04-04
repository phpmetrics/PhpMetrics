<?php
declare(strict_types=1);

namespace Tests\Hal\Application\Config\File;

use Hal\Application\Config\Config;
use Hal\Application\Config\File\ConfigFileReaderIni;
use Hal\Component\File\ReaderInterface;
use Hal\Exception\ConfigException\ConfigFileReadingException;
use Phake;
use PHPUnit\Framework\TestCase;

final class ConfigFileReaderIniTest extends TestCase
{
    /**
     * Ensure the expected exception occurs when trying to read a file that is not an Ini file.
     */
    public function testICantParseIniFile(): void
    {
        $fileReader = Phake::mock(ReaderInterface::class);
        $configFilePath = '/test/config/foo_bar.ini';

        Phake::when($fileReader)->__call('readIni', [$configFilePath])->thenReturn(false);
        $this->expectExceptionObject(ConfigFileReadingException::inIni($configFilePath));

        $config = new Config();
        $reader = new ConfigFileReaderIni($configFilePath, $fileReader);
        $reader->read($config);

        Phake::verify($fileReader)->__call('readIni', [$configFilePath]);
        Phake::verifyNoOtherInteractions($fileReader);
    }

    /**
     * Ensure the ini file is parsed and configuration is loaded.
     */
    public function testICanParseIniFile(): void
    {
        $fileReader = Phake::mock(ReaderInterface::class);
        $configFilePath = '/test/config/foo_bar.ini';
        $fakeConfig = [
            'includes' => ['Controller'],
            'exclude' => 'tests,Tests',
            'report' => [
                'html' => '/tmp/report/',
                'csv' => '/tmp/report.csv',
                'json' => 'tmp/report.json',
                'violations' => '/tmp/violations.xml',
            ]
        ];
        Phake::when($fileReader)->__call('readIni', [$configFilePath])->thenReturn($fakeConfig);

        $config = new Config();
        $reader = new ConfigFileReaderIni($configFilePath, $fileReader);
        $reader->read($config);

        $expectedConfig = [
            'files' => ['/test/config/Controller'],
            'composer' => true,
            'report-html' => '/tmp/report/',
            'report-csv' => '/tmp/report.csv',
            'report-json' => '/test/config/tmp/report.json',
            'report-violations' => '/tmp/violations.xml',
        ];

        self::assertSame($expectedConfig, $config->all());

        Phake::verify($fileReader)->__call('readIni', [$configFilePath]);
        Phake::verifyNoOtherInteractions($fileReader);
    }
}
