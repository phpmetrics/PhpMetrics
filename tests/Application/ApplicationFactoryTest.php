<?php
declare(strict_types=1);

namespace Tests\Hal\Application;

use Generator;
use Hal\Application\ApplicationFactory;
use Hal\Application\ApplicationInterface;
use Hal\Application\Config\Config;
use Hal\Application\Config\ConfigBagInterface;
use Hal\Application\ErrorApplication;
use Hal\Application\HelpApplication;
use Hal\Application\MetricsApplication;
use Hal\Application\VersionApplication;
use Hal\Component\Output\Output;
use Phake;
use PHPUnit\Framework\TestCase;

final class ApplicationFactoryTest extends TestCase
{
    /**
     * @return Generator<string, array{0: ConfigBagInterface, 1: null|class-string<ApplicationInterface>}>
     */
    public function provideConfigurationToDetectApplication(): Generator
    {
        $config = new Config();
        $config->set('help', true);
        yield 'Help application' => [$config, HelpApplication::class];

        $config = new Config();
        $config->set('metrics', true);
        yield 'Metrics application' => [$config, MetricsApplication::class];

        $config = new Config();
        $config->set('version', true);
        yield 'Version application' => [$config, VersionApplication::class];

        $config = new Config();
        $config->set('config-error', 'There is an error');
        yield 'Error application' => [$config, ErrorApplication::class];

        yield 'Main application' => [new Config(), null];
    }

    /**
     * @dataProvider provideConfigurationToDetectApplication
     * @param ConfigBagInterface $config
     * @param null|class-string<ApplicationInterface> $expectedApplication
     * @return void
     */
    //#[DataProvider('provideConfigurationToDetectApplication')] TODO: PHPUnit 10.
    public function testICanRunApplicationFactory(ConfigBagInterface $config, null|string $expectedApplication): void
    {
        $mockOutput = Phake::mock(Output::class);

        $appFactory = new ApplicationFactory($mockOutput);
        $app = $appFactory->buildFromConfig($config);

        if (null === $expectedApplication) {
            self::assertNull($app);
        } else {
            self::assertInstanceOf($expectedApplication, $app);
        }
        Phake::verifyNoInteraction($mockOutput);
    }
}
