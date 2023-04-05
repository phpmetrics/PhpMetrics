<?php
declare(strict_types=1);

namespace Tests\Hal\Application\Config;

use Generator;
use Hal\Application\Config\Config;
use Hal\Application\Config\File\ConfigFileReaderFactoryInterface;
use Hal\Application\Config\File\ConfigFileReaderInterface;
use Hal\Application\Config\Parser;
use Phake;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

final class ParserTest extends TestCase
{
    /**
     * Provide different couple of arguments with the related expected configuration.
     *
     * @return Generator<string, array{array<int, string>, array<string, mixed>, array<string, callable(Config):void>}>
     */
    public static function provideArguments(): Generator
    {
        yield 'No argument' => [[], []];
        yield 'Ignore *.php as arg0' => [['test.php'], []];
        yield 'Ignore *phpmetrics as arg0' => [['run_phpmetrics'], []];
        yield 'Ignore phpmetrics as arg0' => [['phpmetrics'], []];
        yield 'Ignore *phpmetrics.phar as arg0' => [['run_phpmetrics.phar'], []];
        yield 'Ignore phpmetrics.phar as arg0' => [['phpmetrics.phar'], []];
        yield 'Arguments with options' => [
            ['--arg1=foo', '--arg2=bar ', '--arg3="baz"', "--arg4='foobar'"],
            ['arg1' => 'foo', 'arg2' => 'bar', 'arg3' => 'baz', 'arg4' => 'foobar']
        ];
        yield 'Arguments without options' => [['--arg1', '--arg2'], ['arg1' => true, 'arg2' => true]];
        yield 'Files list (no -- trick)' => [['src,application,bin'], ['files' => ['src', 'application', 'bin']]];
        yield 'Files list (with -- trick)' => [['--'], []];

        $whatConfigReadingDoes = static function (Config $config): void {
            $config->set('files', ['/tmp/foo', '/tmp/bar']);
            $config->set(
                'groups',
                [
                    ['name' => 'Component', 'match' => '!component!i'],
                    ['name' => 'Reporters', 'match' => '!Report!'],
                ]
            );
            $config->set('extensions', 'php,php.inc,php8');
            $config->set('composer', true);
            $config->set('exclude', 'tests,Tests');
            $config->set('report-html', '/tmp/foo/report.html');
            $config->set('report-csv', '/tmp/foo/report.csv');
        };
        $expectedConfigFromJson = [
            'files' => ['/tmp/foo', '/tmp/bar'],
            'groups' => [
                ['name' => 'Component', 'match' => '!component!i'],
                ['name' => 'Reporters', 'match' => '!Report!'],
            ],
            'extensions' => 'php,php.inc,php8',
            'composer' => true,
            'exclude' => 'tests,Tests',
            'report-html' => '/tmp/foo/report.html',
            'report-csv' => '/tmp/foo/report.csv',
        ];
        $injectedArgv = ['--config=/test/config/input'];
        yield 'Using config file' => [
            $injectedArgv,
            $expectedConfigFromJson,
            ['/test/config/input' => $whatConfigReadingDoes]
        ];

        $whatConfigReadingDoes = static function (Config $config): void {
            $config->set('files', ['/tmp/foo', '/tmp/bar']);
            $config->set('extensions', 'html');
            $config->set('composer', false);
            $config->set('exclude', 'tests,Tests');
        };
        $expectedConfigFromJson = [
            'files' => ['/and', '/files', 'too'],
            'extensions' => 'html',
            'composer' => false,
            'exclude' => 'exclusion,rule,has,changed',
            'report-json' => '/report/json',
            'quiet' => true,
        ];

        $injectedArgv = [
            'phpmetrics',
            '--config=/test/config/input-multiple',
            '--report-json=/report/json',
            '--quiet',
            '--exclude=exclusion,rule,has,changed',
            '/and,/files,too'
        ];
        yield 'Multiple kind of arguments' => [
            $injectedArgv,
            $expectedConfigFromJson,
            ['/test/config/input-multiple' => $whatConfigReadingDoes]
        ];
    }

    /**
     * Test that the parsing of the arguments is working as expected, including different business rules of parsing the
     * arguments.
     *
     * @param array<int, string> $argv
     * @param array<string, mixed> $expectedConfig
     * @param array<string, callable(Config):void> $whatConfigReadingDoes
     */
    #[DataProvider('provideArguments')]
    public function testTheParsingOfArguments(
        array $argv,
        array $expectedConfig,
        array $whatConfigReadingDoes = []
    ): void {
        $configFileReaderFactory = Phake::mock(ConfigFileReaderFactoryInterface::class);
        $configFileReader = Phake::mock(ConfigFileReaderInterface::class);
        if ([] !== $whatConfigReadingDoes) {
            foreach ($whatConfigReadingDoes as $configFileName => $callback) {
                Phake::when($configFileReaderFactory)->__call('createFromFileName', [$configFileName])
                    ->thenReturn($configFileReader);
                Phake::when($configFileReader)->__call('read', [Phake::anyParameters()])->thenReturnCallback($callback);
            }
        }

        self::assertSame($expectedConfig, (new Parser($configFileReaderFactory))->parse($argv)->all());

        if ([] !== $whatConfigReadingDoes) {
            foreach ($whatConfigReadingDoes as $configFileName => $callback) {
                Phake::verify($configFileReaderFactory)->__call('createFromFileName', [$configFileName]);
                Phake::verify($configFileReader)->__call('read', [Phake::anyParameters()]);
            }
        }

        Phake::verifyNoOtherInteractions($configFileReaderFactory);
        Phake::verifyNoOtherInteractions($configFileReader);
    }
}
