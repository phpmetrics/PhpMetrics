<?php
declare(strict_types=1);

namespace Tests\Hal\Application;

use Generator;
use Hal\Application\Bootstrap;
use Hal\Application\Config\ConfigBagInterface;
use Hal\Application\Config\ParserInterface;
use Hal\Application\Config\ValidatorInterface;
use Hal\Component\Output\Output;
use Hal\Exception\ConfigException\FileDoesNotExistException;
use Phake;
use PHPUnit\Framework\TestCase;
use function array_map;

final class BootstrapTest extends TestCase
{
    /**
     * @return Generator<string, array{0: bool}>
     */
    public function provideValidConfigurations(): Generator
    {
        yield 'In normal verbosity mode' => [false];
        yield 'In quiet mode' => [true];
    }

    /**
     * @dataProvider provideValidConfigurations
     * @param bool $quietMode
     * @return void
     */
    //#[DataProvider('provideValidConfigurations')] // TODO PHPUnit 10.
    public function testICanBootstrapWithValidConfiguration(bool $quietMode): void
    {
        $mocks = [
            'config' => Phake::mock(ConfigBagInterface::class),
            'parser' => Phake::mock(ParserInterface::class),
            'validator' => Phake::mock(ValidatorInterface::class),
            'output' => Phake::mock(Output::class),
        ];
        $argv = [];

        $bootstrap = new Bootstrap(
            $mocks['parser'],
            $mocks['validator'],
            $mocks['output']
        );

        Phake::when($mocks['parser'])->__call('parse', [$argv])->thenReturn($mocks['config']);
        Phake::when($mocks['validator'])->__call('validate', [$mocks['config']])->thenDoNothing();
        Phake::when($mocks['config'])->__call('has', ['quiet'])->thenReturn($quietMode);
        Phake::when($mocks['output'])->__call('setQuietMode', [true])->thenDoNothing();

        $actualConfig = $bootstrap->prepare($argv);

        self::assertSame($mocks['config'], $actualConfig);
        Phake::verify($mocks['parser'])->__call('parse', [$argv]);
        Phake::verify($mocks['validator'])->__call('validate', [$mocks['config']]);
        Phake::verify($mocks['config'])->__call('has', ['quiet']);
        if ($quietMode) {
            Phake::verify($mocks['output'])->__call('setQuietMode', [true]);
        }
        array_map(Phake::verifyNoOtherInteractions(...), $mocks);
    }

    /**
     * @return Generator<string, array{0: bool, 1: bool}>
     */
    public function provideInvalidConfigurations(): Generator
    {
        yield 'Configuration requires for help only' => [true, false];
        yield 'Configuration requires for version only' => [false, true];
        yield 'Configuration requires for both help and version' => [true, true];
        yield 'Configuration is just invalid' => [false, false];
    }

    /**
     * @dataProvider provideInvalidConfigurations
     * @param bool $isHelp
     * @param bool $isVersion
     * @return void
     */
    //#[DataProvider('provideInvalidConfigurations')] // TODO PHPUnit 10.
    public function testICanBootstrapWithInvalidConfiguration(bool $isHelp, bool $isVersion): void
    {
        $mocks = [
            'config' => Phake::mock(ConfigBagInterface::class),
            'parser' => Phake::mock(ParserInterface::class),
            'validator' => Phake::mock(ValidatorInterface::class),
            'output' => Phake::mock(Output::class),
        ];
        $argv = [];

        $bootstrap = new Bootstrap(
            $mocks['parser'],
            $mocks['validator'],
            $mocks['output']
        );

        Phake::when($mocks['parser'])->__call('parse', [$argv])->thenReturn($mocks['config']);
        Phake::when($mocks['validator'])->__call('validate', [$mocks['config']])
            ->thenThrow(new FileDoesNotExistException('Dummy error'));
        Phake::when($mocks['config'])->__call('has', ['help'])->thenReturn($isHelp);
        Phake::when($mocks['config'])->__call('has', ['version'])->thenReturn($isVersion);
        Phake::when($mocks['config'])->__call('set', [Phake::anyParameters()])->thenDoNothing();

        $actualConfig = $bootstrap->prepare($argv);

        self::assertSame($mocks['config'], $actualConfig);
        Phake::verify($mocks['parser'])->__call('parse', [$argv]);
        Phake::verify($mocks['validator'])->__call('validate', [$mocks['config']]);
        Phake::verify($mocks['config'])->__call('has', ['help']);
        if (!$isHelp) {
            Phake::verify($mocks['config'])->__call('has', ['version']);
            if (!$isVersion) {
                Phake::verify($mocks['config'])->__call('set', ['config-error', 'Dummy error']);
            }
        }
        array_map(Phake::verifyNoOtherInteractions(...), $mocks);
    }
}
