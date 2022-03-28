<?php
declare(strict_types=1);

namespace Tests\Hal\Application\Config;

use Generator;
use Hal\Application\Config\Config;
use Hal\Application\Config\Validator;
use Hal\Exception\ConfigException;
use Hal\Metric\Group\Group;
use PHPUnit\Framework\TestCase;
use function array_map;
use function sprintf;

final class ValidatorTest extends TestCase
{
    /**
     * Ensure the expected exception is thrown when trying to validate a configuration without any file.
     */
    public function testICantValidateConfigurationWithoutFiles(): void
    {
        $config = new Config();

        $this->expectException(ConfigException::class);
        $this->expectExceptionMessage('Directory to parse is missing or incorrect');

        (new Validator())->validate($config);
    }

    /**
     * Ensure the expected exception is thrown when trying to validate a configuration with unknown files.
     */
    public function testICantValidateConfigurationWithUnknownFiles(): void
    {
        $unknownFilePath = '/this/path/does/not/exist';
        self::assertFileDoesNotExist($unknownFilePath);

        $config = new Config();
        $config->set('files', [$unknownFilePath]);

        $this->expectException(ConfigException::class);
        $this->expectExceptionMessage(sprintf('Directory %s does not exist', $unknownFilePath));

        (new Validator())->validate($config);
    }

    /**
     * Provide test cases for configurations that must be non-empty strings, or non-empty arrays. Of course, as we want
     * to test error cases, this provider is only giving wrong elements.
     *
     * @return Generator<string, array{0: string, 1: mixed}>
     */
    public function provideBadlyFormattedConfigurations(): Generator
    {
        $wrongValues = [
            'report-html' => [42, ''],
            'report-csv' => [42, ''],
            'report-violation' => [42, ''],
            'report-json' => [42, ''],
            'config' => [42, ''],
        ];

        foreach ($wrongValues as $key => $wrongValueList) {
            foreach ($wrongValueList as $i => $wrongValue) {
                yield 'Bad configuration "' . $key . '": using wrong value #' . $i => [$key, $wrongValue];
            }
        }
    }

    /**
     * Ensure the expected exception is thrown when some configurations are not in the expected format.
     *
     * @dataProvider provideBadlyFormattedConfigurations
     * @param string $configKey The configuration key to check its format.
     * @param mixed $badValue The wrong value set to trigger the exception.
     */
    //#[DataProvider('provideBadlyFormattedConfigurations')] // TODO PHPUnit 10: use attribute instead of annotation.
    public function testICantValidateBadlyFormattedConfiguration(string $configKey, mixed $badValue): void
    {
        $config = new Config();
        $config->set('files', ['/tmp']);
        $config->set($configKey, $badValue);

        $this->expectException(ConfigException::class);
        $this->expectExceptionMessage(sprintf('%s option requires a value', $configKey));

        (new Validator())->validate($config);
    }

    /**
     * Provides valid configuration instances with the associated expected configuration bag the validator must have
     * normalized.
     *
     * @return Generator<string, array{0: Config, 1: array<string, mixed>}>
     */
    public function provideConfigurations(): Generator
    {
        // Minimum configuration
        $config = new Config();
        $config->set('files', ['/tmp']);

        $expectedConfiguration = [
            'files' => ['/tmp'],
            'extensions' => ['php', 'inc'],
            'exclude' => [
                'vendor', 'test', 'Test', 'tests', 'Tests', 'testing', 'Testing', 'bower_components', 'node_modules',
                'cache', 'spec'
            ],
            'groups' => []
        ];

        yield 'With minimum configuration' => [$config, $expectedConfiguration];

        // Complete configuration
        $config = new Config();
        $config->set('files', ['/tmp']);
        $config->set('extensions', 'a,b,c,d');
        $config->set('exclude', 'foo,,bar,baz,test');
        $config->set('groups', [
            ['name' => 'App', 'match' => '#Application#i'],
            ['name' => 'Src', 'match' => '!sources!'],
            ['name' => 'Vendor', 'match' => '!vendor!'],
        ]);

        $expectedConfiguration = [
            'files' => ['/tmp'],
            'extensions' => ['a', 'b', 'c', 'd'],
            'exclude' => ['foo', 2 => 'bar', 'baz', 'test'],
            'groups' => [
                new Group('App', '#Application#i'),
                new Group('Src', '!sources!'),
                new Group('Vendor', '!vendor!'),
            ]
        ];

        yield 'With complete configuration' => [$config, $expectedConfiguration];

        // Complete configuration
        $config = new Config();
        $config->set('files', ['/tmp']);
        $config->set('extensions', 'a,b,c,d');
        $config->set('exclude', ['foo', '', 'bar', 'baz', 'test', '']);
        $config->set('groups', [
            ['name' => 'App', 'match' => '#Application#i'],
            ['name' => 'Src', 'match' => '!sources!'],
            ['name' => 'Vendor', 'match' => '!vendor!'],
        ]);

        $expectedConfiguration = [
            'files' => ['/tmp'],
            'extensions' => ['a', 'b', 'c', 'd'],
            'exclude' => ['foo', 2 => 'bar', 'baz', 'test'],
            'groups' => [
                new Group('App', '#Application#i'),
                new Group('Src', '!sources!'),
                new Group('Vendor', '!vendor!'),
            ]
        ];

        yield 'With complete configuration, but using deprecated "exclude"' => [$config, $expectedConfiguration];
    }

    /**
     * Test that the given configurations are valid, and normalized.
     *
     * @dataProvider provideConfigurations
     * @param Config $config The configuration instance to validate.
     * @param array<string, mixed> $expectedConfiguration The validated and normalized configuration data.
     * @throws ConfigException When configuration instance is not valid, but in this test case, it is always valid.
     */
    //#[DataProvider('provideConfigurations')] // TODO PHPUnit 10: use attribute instead of annotation.
    public function testICanValidateConfiguration(Config $config, array $expectedConfiguration): void
    {
        (new Validator())->validate($config);
        $actualConfiguration = $config->all();

        // Manage the groups on a second time as they are objects and can't be tested to be the same (they're different
        // instances).
        $expectedGroup = $expectedConfiguration['groups'];
        unset($actualConfiguration['groups'], $expectedConfiguration['groups']);

        self::assertSame($expectedConfiguration, $actualConfiguration);
        array_map(static function (Group $expectedGroup, Group $actualGroup): void {
            self::assertSame($expectedGroup->name, $actualGroup->name);
            self::assertSame($expectedGroup->getRegex(), $actualGroup->getRegex());
        }, $expectedGroup, $config->get('groups'));
    }

    /**
     * Simply check the "help" disclaimer isn't changing without updating the unit tests.
     */
    public function testHelp(): void
    {
        $expectedHelp = <<<EOT
Usage:

    phpmetrics [...options...] <directories>

Required:

    <directories>                     List of directories to parse, separated by a comma (,)

Optional:

    --config=<file>                   Use a file for configuration
    --exclude=<directory>             List of directories to exclude, separated by a comma (,)
    --extensions=<php,inc>            List of extensions to parse, separated by a comma (,)
    --report-html=<directory>         Folder where report HTML will be generated
    --report-csv=<file>               File where report CSV will be generated
    --report-json=<file>              File where report Json will be generated
    --report-violations=<file>        File where XML violations report will be generated
    --git[=</path/to/git_binary>]     Perform analyses based on Git History (default binary path: "git")
    --junit[=</path/to/junit.xml>]    Evaluates metrics according to JUnit logs
    --quiet                           Enable the quiet mode
    --version                         Display current version

Examples:

    phpmetrics --report-html="./report" ./src

        Analyse the "./src" directory and generate an HTML report on the "./report" folder

    phpmetrics --report-violations="./build/violations.xml" ./src,./lib

        Analyse the "./src" and "./lib" directories, and generate the "./build/violations.xml" file. This file could
        be read by any Continuous Integration Platform, and follows the "PMD Violation" standards.

EOT;
        self::assertSame($expectedHelp, Validator::help());
    }
}
