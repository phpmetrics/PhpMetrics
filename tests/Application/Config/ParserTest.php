<?php
declare(strict_types=1);

namespace Tests\Hal\Application\Config;

use Generator;
use Hal\Application\Config\Parser;
use PHPUnit\Framework\TestCase;
use function dirname;
use function realpath;

final class ParserTest extends TestCase
{
    /**
     * Provide different couple of arguments with the related expected configuration.
     *
     * @return Generator<string, array{0:array<int, string>, 1:array<string, mixed>}>
     */
    public function provideArguments(): Generator
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

        $resourceTestRootDir = realpath(dirname(__DIR__, 2)) . '/resources';
        $testConfigJsonPath = $resourceTestRootDir . '/test_config.json';
        $expectedConfigFromJson = [
            'files' => [$resourceTestRootDir . '/Controller', '/src/other/files'],
            'groups' => [
                ['name' => 'Component', 'match' => '!component!i'],
                ['name' => 'Reporters', 'match' => '!Report!'],
            ],
            'extensions' => 'php,php.inc,php8',
            'exclude' => 'tests,Tests',
            'git' => 'git',
            'junit' => '/tmp/junit.xml',
            'report-html' => $resourceTestRootDir . '/report/with/relative/path',
            'report-csv' => '/report/with/absolute/path',
        ];
        $injectedArgv = ['--config=' . $testConfigJsonPath];
        yield 'Using config file' => [$injectedArgv, $expectedConfigFromJson];

        $injectedArgv = [
            'phpmetrics',
            '--config=' . $testConfigJsonPath,
            '--report-json=/report/json',
            '--quiet',
            '--exclude=exclusion,rule,has,changed',
            '/and,/files,too'
        ];
        $expectedConfigFromJson['files'] = ['/and', '/files', 'too'];
        $expectedConfigFromJson['exclude'] = 'exclusion,rule,has,changed';
        $expectedConfigFromJson['report-json'] = '/report/json';
        $expectedConfigFromJson['quiet'] = true;
        yield 'Multiple kind of arguments' => [$injectedArgv, $expectedConfigFromJson];
    }

    /**
     * Test that the parsing of the arguments is working as expected, including different business rules of parsing the
     * arguments.
     *
     * @dataProvider provideArguments
     * @param array<int, string> $argv
     * @param array<string, mixed> $expectedConfig
     */
    public function testTheParsingOfArguments(array $argv, array $expectedConfig): void
    {
        self::assertSame($expectedConfig, (new Parser())->parse($argv)->all());
    }
}
