<?php
declare(strict_types=1);

namespace Tests\Hal\Metric\System\Packages\Composer;

use Hal\Component\File\FinderInterface;
use Hal\Component\File\ReaderInterface;
use Hal\Metric\Metrics;
use Hal\Metric\ProjectMetric;
use Hal\Metric\System\Packages\Composer\Composer;
use Hal\Metric\System\Packages\Composer\ComposerRegistryConnectorInterface;
use Phake;
use PHPUnit\Framework\TestCase;
use stdClass;
use function array_keys;
use function array_map;

final class ComposerTest extends TestCase
{
    /**
     * @return void
     */
    public function testCalculationOfComposerPackagesRequiredAndInstalled(): void
    {
        $metricsMock = Phake::mock(Metrics::class);
        $composerJsonFinderMock = Phake::mock(FinderInterface::class);
        $composerLockFinderMock = Phake::mock(FinderInterface::class);
        $composerRegistryConnectorMock = Phake::mock(ComposerRegistryConnectorInterface::class);
        $fileReader = Phake::mock(ReaderInterface::class);
        $composerJsonFiles = [
            '/test/composer/uselessFile.txt' => 'This file has only unit test purpose.',
            '/test/composer/test-composer.json' => [
                'name' => 'test',
                'require' => [
                    'php' => '>=8.1',
                    'ext-test' => '*',
                    'foo/foo' => '^1',
                    'foo/bar' => '^1.0',
                    'foo/baz' => '^1.0.0',
                    'test/foo' => '~1|~2',
                    'test/bar' => '~1.0|~2.0',
                    'test/baz' => '~1.0.0|~2.0.0',
                ],
            ],
            '/test/composer/composer.json.dist' => [
                'name' => 'dist',
                'require' => [
                    'php' => '>=8.1',
                    'ext-test' => '*',
                    'dist/foo' => '>=5.0',
                    'dist/bar' => '<=4.99.99',
                    'dist/baz' => '3.4.5',
                    'not/installed' => '0',
                ],
            ],
            '/test/composer/test-composer-dist.json' => [
                'name' => 'test-dist',
                'require' => [
                    'php' => '>=8.1',
                    'ext-test' => '*',
                    'foo/baz' => '^1.0.0',
                    'test/baz' => '~1.0.0|~2.0.0',
                    'dist/baz' => '3.4.5',
                ],
            ],
            '/test/composer/no-require.composer.json' => [
                'name' => 'no-require',
            ],
        ];
        $composerLockFiles = [
            '/test/composer/test-composer.lock' => [
                'packages' => [
                    ['name' => 'foo/foo', 'version' => '1.0.0'],
                    ['name' => 'foo/bar', 'version' => '1.1.0'],
                    ['name' => 'foo/baz', 'version' => 'v1.0.34'],
                    ['name' => 'test/foo', 'version' => '2.4.5'],
                    ['name' => 'test/bar', 'version' => '2.0.3'],
                    ['name' => 'test/baz', 'version' => 'v1.0.33'],
                ],
            ],
            '/test/composer/composer.lock.dist' => [
                'packages' => [
                    ['name' => 'dist/foo', 'version' => 'v6.9.4'],
                    ['name' => 'dist/bar', 'version' => '4.30.87'],
                    ['name' => 'dist/baz', 'version' => '3.4.5'],
                ],
            ],
            '/test/composer/uselessLockFile.txt' => 'This file has only unit test purpose.',
            '/test/composer/no-packages.composer.lock' => [],
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
        Phake::when($composerJsonFinderMock)->__call('fetch', [['foo', 'bar', './']])->thenReturn(
            array_keys($composerJsonFiles)
        );
        Phake::when($composerLockFinderMock)->__call('fetch', [['foo', 'bar', './']])->thenReturn(
            array_keys($composerLockFiles)
        );
        foreach ($packageDataFromRegistry as $requirementName => $packageData) {
            Phake::when($composerRegistryConnectorMock)->__call('get', [$requirementName])->thenReturn($packageData);
        }
        foreach ([...$composerJsonFiles, ...$composerLockFiles] as $file => $content) {
            Phake::when($fileReader)->__call('readJson', [$file])->thenReturn($content);
        }

        (new Composer(
            $metricsMock,
            null,
            ['foo', 'bar'],
            $composerJsonFinderMock,
            $composerLockFinderMock,
            $fileReader,
            $composerRegistryConnectorMock
        ))->calculate();

        Phake::verify($metricsMock)->__call('attach', [$projectMetricCollector]);
        Phake::verify($composerJsonFinderMock)->__call('fetch', [['foo', 'bar', './']]);
        Phake::verify($composerLockFinderMock)->__call('fetch', [['foo', 'bar', './']]);
        foreach (array_keys($packageDataFromRegistry) as $requirementName) {
            Phake::verify($composerRegistryConnectorMock)->__call('get', [$requirementName]);
        }
        // Only filenames containing "composer[-dist].json" or "composer.lock" are read from the reader.
        Phake::verify($fileReader, Phake::never())->__call('readJson', ['/test/composer/uselessFile.txt']);
        Phake::verify($fileReader, Phake::never())->__call('readJson', ['/test/composer/uselessLockFile.txt']);
        Phake::verify($fileReader)->__call('readJson', ['/test/composer/test-composer.json']);
        Phake::verify($fileReader)->__call('readJson', ['/test/composer/composer.json.dist']);
        Phake::verify($fileReader)->__call('readJson', ['/test/composer/test-composer-dist.json']);
        Phake::verify($fileReader)->__call('readJson', ['/test/composer/no-require.composer.json']);
        Phake::verify($fileReader)->__call('readJson', ['/test/composer/test-composer.lock']);
        Phake::verify($fileReader)->__call('readJson', ['/test/composer/composer.lock.dist']);
        Phake::verify($fileReader)->__call('readJson', ['/test/composer/no-packages.composer.lock']);

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
        Phake::verifyNoOtherInteractions($fileReader);
    }

    /**
     * @return void
     */
    public function testCalculationOfComposerIsDisabled(): void
    {
        $metricsMock = Phake::mock(Metrics::class);
        $composerJsonFinderMock = Phake::mock(FinderInterface::class);
        $composerLockFinderMock = Phake::mock(FinderInterface::class);
        $fileReader = Phake::mock(ReaderInterface::class);
        $composerRegistryConnectorMock = Phake::mock(ComposerRegistryConnectorInterface::class);

        (new Composer(
            $metricsMock,
            false,
            [],
            $composerJsonFinderMock,
            $composerLockFinderMock,
            $fileReader,
            $composerRegistryConnectorMock
        ))->calculate();

        Phake::verifyNoInteraction($composerJsonFinderMock);
        Phake::verifyNoInteraction($composerLockFinderMock);
        Phake::verifyNoInteraction($composerRegistryConnectorMock);
        Phake::verifyNoInteraction($metricsMock);
        Phake::verifyNoInteraction($fileReader);
    }
}
