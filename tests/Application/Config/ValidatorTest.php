<?php
declare(strict_types=1);

namespace Tests\Hal\Application\Config;

use Generator;
use Hal\Application\Config\Config;
use Hal\Application\Config\Validator;
use Hal\Component\File\SystemInterface;
use Hal\Exception\ConfigException;
use Hal\Metric\Group\Group;
use Hal\Search\SearchesValidatorInterface;
use Hal\Search\SearchInterface;
use Phake;
use PHPUnit\Framework\Attributes\DataProvider;
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
        $searchesValidator = Phake::mock(SearchesValidatorInterface::class);
        $fileSystem = Phake::mock(SystemInterface::class);

        $this->expectException(ConfigException::class);
        $this->expectExceptionMessage('Directory to parse is missing or incorrect');

        (new Validator($searchesValidator, $fileSystem))->validate($config);

        Phake::verifyNoInteraction($searchesValidator);
        Phake::verifyNoInteraction($fileSystem);
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
        $searchesValidator = Phake::mock(SearchesValidatorInterface::class);
        $fileSystem = Phake::mock(SystemInterface::class);
        Phake::when($fileSystem)->__call('exists', [$unknownFilePath])->thenReturn(false);

        $this->expectException(ConfigException::class);
        $this->expectExceptionMessage(sprintf('Directory %s does not exist', $unknownFilePath));

        (new Validator($searchesValidator, $fileSystem))->validate($config);

        Phake::verifyNoInteraction($searchesValidator);
        Phake::verify($fileSystem)->__call('exists', [$unknownFilePath]);
        Phake::verifyNoOtherInteractions($fileSystem);
    }

    /**
     * Provide test cases for configurations that must be non-empty strings, or non-empty arrays. Of course, as we want
     * to test error cases, this provider is only giving wrong elements.
     *
     * @return Generator<string, array{string, mixed}>
     */
    public static function provideBadlyFormattedConfigurations(): Generator
    {
        $wrongValues = [
            'report-html' => [42, ''],
            'report-csv' => [42, ''],
            'report-violations' => [42, ''],
            'report-json' => [42, ''],
            'report-openmetrics' => [42, ''],
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
     * @param string $configKey The configuration key to check its format.
     * @param mixed $badValue The wrong value set to trigger the exception.
     */
    #[DataProvider('provideBadlyFormattedConfigurations')]
    public function testICantValidateBadlyFormattedConfiguration(string $configKey, mixed $badValue): void
    {
        $config = new Config();
        $config->set('files', ['/tmp']);
        $config->set($configKey, $badValue);
        $searchesValidator = Phake::mock(SearchesValidatorInterface::class);
        Phake::when($searchesValidator)->__call('validates', [Phake::anyParameters()])->thenDoNothing();
        $fileSystem = Phake::mock(SystemInterface::class);
        Phake::when($fileSystem)->__call('exists', ['/tmp'])->thenReturn(true);

        $this->expectException(ConfigException::class);
        $this->expectExceptionMessage(sprintf('%s option requires a value', $configKey));

        (new Validator($searchesValidator, $fileSystem))->validate($config);

        Phake::verify($fileSystem)->__call('exists', ['/tmp']);
        Phake::verifyNoOtherInteractions($fileSystem);
    }

    /**
     * Provides valid configuration instances with the associated expected configuration bag the validator must have
     * normalized.
     *
     * @return Generator<string, array{Config, array<string, mixed>}>
     */
    public static function provideConfigurations(): Generator
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
            'groups' => [],
            'composer' => true,
            'searches' => [],
        ];

        yield 'With minimum configuration' => [$config, $expectedConfiguration];

        // Minimum configuration
        $config = new Config();
        $config->set('files', ['/tmp']);
        $config->set('composer', 'false');

        $expectedConfiguration = [
            'files' => ['/tmp'],
            'composer' => false,
            'extensions' => ['php', 'inc'],
            'exclude' => [
                'vendor', 'test', 'Test', 'tests', 'Tests', 'testing', 'Testing', 'bower_components', 'node_modules',
                'cache', 'spec'
            ],
            'groups' => [],
            'searches' => [],
        ];

        yield 'With minimum configuration, but composer is disabled' => [$config, $expectedConfiguration];

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
        $searchMocks = [
            Phake::mock(SearchInterface::class),
            Phake::mock(SearchInterface::class),
            Phake::mock(SearchInterface::class),
        ];
        $config->set('searches', $searchMocks);

        $expectedConfiguration = [
            'files' => ['/tmp'],
            'extensions' => ['a', 'b', 'c', 'd'],
            'exclude' => ['foo', 2 => 'bar', 'baz', 'test'],
            'groups' => [
                new Group('App', '#Application#i'),
                new Group('Src', '!sources!'),
                new Group('Vendor', '!vendor!'),
            ],
            'searches' => $searchMocks,
            'composer' => true,
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
        $searchMocks = [Phake::mock(SearchInterface::class)];
        $config->set('searches', $searchMocks);

        $expectedConfiguration = [
            'files' => ['/tmp'],
            'extensions' => ['a', 'b', 'c', 'd'],
            'exclude' => ['foo', 2 => 'bar', 'baz', 'test'],
            'groups' => [
                new Group('App', '#Application#i'),
                new Group('Src', '!sources!'),
                new Group('Vendor', '!vendor!'),
            ],
            'searches' => $searchMocks,
            'composer' => true,
        ];

        yield 'With complete configuration, but using deprecated "exclude"' => [$config, $expectedConfiguration];
    }

    /**
     * Test that the given configurations are valid, and normalized.
     *
     * @param Config $config The configuration instance to validate.
     * @param array<string, mixed> $expectedConfiguration The validated and normalized configuration data.
     */
    #[DataProvider('provideConfigurations')]
    public function testICanValidateConfiguration(Config $config, array $expectedConfiguration): void
    {
        $searchesValidator = Phake::mock(SearchesValidatorInterface::class);
        Phake::when($searchesValidator)->__call('validates', [Phake::anyParameters()])->thenDoNothing();
        $fileSystem = Phake::mock(SystemInterface::class);
        Phake::when($fileSystem)->__call('exists', ['/tmp'])->thenReturn(true);

        (new Validator($searchesValidator, $fileSystem))->validate($config);
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

        Phake::verify($searchesValidator)->__call('validates', [$config->get('searches')]);
        Phake::verify($fileSystem)->__call('exists', ['/tmp']);
        Phake::verifyNoOtherInteractions($searchesValidator);
        Phake::verifyNoOtherInteractions($fileSystem);
    }
}
