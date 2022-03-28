<?php
declare(strict_types=1);

namespace Tests\Hal\Metric\System\Packages\Composer;

use Hal\Component\File\FinderInterface;
use Hal\Metric\Metrics;
use Hal\Metric\ProjectMetric;
use Hal\Metric\System\Packages\Composer\Composer;
use Hal\Metric\System\Packages\Composer\ComposerRegistryConnectorInterface;
use JsonException;
use Phake;
use PHPUnit\Framework\TestCase;
use stdClass;
use function array_keys;
use function array_map;
use function dirname;

final class ComposerTest extends TestCase
{
    /**
     * @return void
     * @throws JsonException Not thrown in this test.
     */
    public function testCalculationOfComposerPackagesRequiredAndInstalled(): void
    {
        $metricsMock = Phake::mock(Metrics::class);
        $composerJsonFinderMock = Phake::mock(FinderInterface::class);
        $composerLockFinderMock = Phake::mock(FinderInterface::class);
        $composerRegistryConnectorMock = Phake::mock(ComposerRegistryConnectorInterface::class);
        $resourceDirectory = dirname(__DIR__, 4) . '/resources/metrics/system/packages/composer';
        $composerJsonFiles = [
            $resourceDirectory . '/uselessFile.txt',
            $resourceDirectory . '/test-composer.json',
            $resourceDirectory . '/composer.json.dist',
            $resourceDirectory . '/test-composer-dist.json',
            $resourceDirectory . '/no-require.composer.json',
        ];
        $composerLockFiles = [
            $resourceDirectory . '/test-composer.lock',
            $resourceDirectory . '/composer.lock.dist',
            $resourceDirectory . '/uselessFile.txt',
            $resourceDirectory . '/no-packages.composer.lock',
        ];
        $packageDataFromRegistry = [
            'foo/foo' => (object)['latest' => '1.0.0'], // 1.0.0 set in fake composer.lock => latest
            'foo/bar' => (object)['latest' => '1.3.9'], // 1.1.0 set in fake composer.lock => outdated
            'foo/baz' => (object)['latest' => null], // => unknown
            'test/foo' => (object)['latest' => '2.4.5'], // 2.4.5 set in fake composer.lock => latest
            'test/bar' => (object)['latest' => '3.33.3'], // 2.0.3 set in fake composer.lock => outdated
            'test/baz' => (object)['latest' => null], // => unknown
            'dist/foo' => (object)['latest' => '6.9.4'], // 6.9.4 set in fake composer.lock => latest
            'dist/bar' => (object)['latest' => '4.30.88'], // 4.30.87 set in fake composer.lock => outdated
            'dist/baz' => (object)['latest' => null], // => unknown
            'not/installed' => (object)['latest' => '0'],
        ];

        $projectMetricCollector = null;

        Phake::when($metricsMock)->__call('attach', [Phake::anyParameters()])->thenReturnCallback(
            static function (ProjectMetric $projectMetric) use (&$projectMetricCollector): void {
                $projectMetricCollector = $projectMetric;
            }
        );
        Phake::when($composerJsonFinderMock)->__call('fetch', [['foo', 'bar', './']])->thenReturn($composerJsonFiles);
        Phake::when($composerLockFinderMock)->__call('fetch', [['foo', 'bar', './']])->thenReturn($composerLockFiles);
        foreach ($packageDataFromRegistry as $requirementName => $packageData) {
            Phake::when($composerRegistryConnectorMock)->__call('get', [$requirementName])->thenReturn($packageData);
        }

        (new Composer(
            $metricsMock,
            ['foo', 'bar'],
            $composerJsonFinderMock,
            $composerLockFinderMock,
            $composerRegistryConnectorMock
        ))->calculate();

        Phake::verify($metricsMock)->__call('attach', [$projectMetricCollector]);
        Phake::verify($composerJsonFinderMock)->__call('fetch', [['foo', 'bar', './']]);
        Phake::verify($composerLockFinderMock)->__call('fetch', [['foo', 'bar', './']]);
        foreach (array_keys($packageDataFromRegistry) as $requirementName) {
            Phake::verify($composerRegistryConnectorMock)->__call('get', [$requirementName]);
        }

        $projectMetricBag = $projectMetricCollector->all();
        self::assertSame('composer', $projectMetricCollector->getName());
        self::assertArrayHasKey('packages', $projectMetricBag);
        self::assertArrayHasKey('packages-installed', $projectMetricBag);

        $expectedPackages = [
            'foo/foo' => (object)[
                'latest' => '1.0.0',
                'installed' => '1.0.0',
                'required' => '^1',
                'name' => 'foo/foo',
                'status' => 'latest',
            ],
            'foo/bar' => (object)[
                'latest' => '1.3.9',
                'installed' => '1.1.0',
                'required' => '^1.0',
                'name' => 'foo/bar',
                'status' => 'outdated',
            ],
            'foo/baz' => (object)[
                'latest' => null,
                'installed' => '1.0.34',
                'required' => '^1.0.0',
                'name' => 'foo/baz',
                'status' => 'unknown',
            ],
            'test/foo' => (object)[
                'latest' => '2.4.5',
                'installed' => '2.4.5',
                'required' => '~1|~2',
                'name' => 'test/foo',
                'status' => 'latest',
            ],
            'test/bar' => (object)[
                'latest' => '3.33.3',
                'installed' => '2.0.3',
                'required' => '~1.0|~2.0',
                'name' => 'test/bar',
                'status' => 'outdated',
            ],
            'test/baz' => (object)[
                'latest' => null,
                'installed' => '1.0.33',
                'required' => '~1.0.0|~2.0.0',
                'name' => 'test/baz',
                'status' => 'unknown',
            ],
            'dist/foo' => (object)[
                'latest' => '6.9.4',
                'installed' => '6.9.4',
                'required' => '>=5.0',
                'name' => 'dist/foo',
                'status' => 'latest',
            ],
            'dist/bar' => (object)[
                'latest' => '4.30.88',
                'installed' => '4.30.87',
                'required' => '<=4.99.99',
                'name' => 'dist/bar',
                'status' => 'outdated',
            ],
            'dist/baz' => (object)[
                'latest' => null,
                'installed' => '3.4.5',
                'required' => '3.4.5',
                'name' => 'dist/baz',
                'status' => 'unknown',
            ],
            'not/installed' => (object)[
                'latest' => '0',
                'installed' => null,
                'required' => '0',
                'name' => 'not/installed',
                'status' => 'unknown',
            ],
        ];
        self::assertSame(array_keys($expectedPackages), array_keys($projectMetricBag['packages']));
        array_map(static function (stdClass $expectedPackage, stdClass $actualPackage): void {
            self::assertSame((array)$expectedPackage, (array)$actualPackage);
        }, $expectedPackages, $projectMetricBag['packages']);

        $expectedInstalledPackages = [
            'foo/foo' => '1.0.0',
            'foo/bar' => '1.1.0',
            'foo/baz' => '1.0.34',
            'test/foo' => '2.4.5',
            'test/bar' => '2.0.3',
            'test/baz' => '1.0.33',
            'dist/foo' => '6.9.4',
            'dist/bar' => '4.30.87',
            'dist/baz' => '3.4.5',
        ];
        self::assertSame($expectedInstalledPackages, $projectMetricBag['packages-installed']);

        Phake::verifyNoOtherInteractions($composerJsonFinderMock);
        Phake::verifyNoOtherInteractions($composerLockFinderMock);
        Phake::verifyNoOtherInteractions($composerRegistryConnectorMock);
        Phake::verifyNoOtherInteractions($metricsMock);
    }
}
