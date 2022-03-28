<?php
declare(strict_types=1);

namespace Tests\Hal\Metric\System\Packages\Composer;

use Exception;
use Generator;
use Hal\Metric\System\Packages\Composer\Packagist;
use JsonException;
use PHPUnit\Framework\TestCase;
use stdClass;
use function random_int;
use const PHP_INT_MAX;

final class PackagistTest extends TestCase
{
    private stdClass $defaultResponse;

    /**
     * {@inheritDoc}
     */
    protected function setUp(): void
    {
        $this->defaultResponse = (object)[
            'name' => '',
            'latest' => null,
            'license' => [],
            'homepage' => null,
            'time' => null,
            'zip' => null,
            'type' => 'unknown',
            'description' => null,
            'github_stars' => 0,
            'github_watchers' => 0,
            'github_forks' => 0,
            'github_open_issues' => 0,
            'download_total' => 0,
            'download_monthly' => 0,
            'download_daily' => 0,
            'favorites' => 0,
        ];
    }

    /**
     * @return void
     * @throws JsonException
     */
    public function testFetchWrongNamePackage(): void
    {
        $response = (new Packagist())->get('foo');
        self::assertEqualsCanonicalizing($this->defaultResponse, $response);
    }

    /**
     * @return Generator<string, array{0: bool}>
     */
    public function provideSwitchOnUsingProxyToContactPackagist(): Generator
    {
        yield 'With proxy' => [true];
        yield 'Without proxy' => [false];
    }

    /**
     * @backupGlobals enabled
     * @dataProvider provideSwitchOnUsingProxyToContactPackagist
     * @param bool $useProxy
     * @return void
     * @throws JsonException
     * @throws Exception
     */
    //#[DataProvider('provideSwitchOnUsingProxyToContactPackagist')] TODO: PHPUnit 10
    //#[BackupGlobals('enabled')] TODO: PHPUnit 10
    public function testFetchNonExistentPackage(bool $useProxy): void
    {
        if ($useProxy) {
            $_SERVER['HTTP_PROXY'] = '0.0.0.0:0';
        }
        $packageName = 'phpmetrics/this_package_does_not_exists_' . random_int(0, PHP_INT_MAX);

        $response = (new Packagist())->get($packageName);
        self::assertEqualsCanonicalizing($this->defaultResponse, $response);
    }

    /**
     * @return void
     * @throws JsonException
     */
    public function testFetchExistentPackage(): void
    {
        $response = (new Packagist())->get('phpmetrics/phpmetrics');
        self::assertSame('phpmetrics/phpmetrics', $response->name);

        // Expect that all default values are replaced by values from PHPMetrics' package.
        // Those values are evolving in the project's lifetime, so there is no reason to try to guess them.
        self::assertNotSame($this->defaultResponse->name, $response->name);
        self::assertNotSame($this->defaultResponse->latest, $response->latest);
        self::assertNotSame($this->defaultResponse->license, $response->license);
        self::assertNotSame($this->defaultResponse->homepage, $response->homepage);
        self::assertNotSame($this->defaultResponse->time, $response->time);
        self::assertNotSame($this->defaultResponse->zip, $response->zip);
        self::assertNotSame($this->defaultResponse->type, $response->type);
        self::assertNotSame($this->defaultResponse->description, $response->description);
        self::assertNotSame($this->defaultResponse->github_stars, $response->github_stars);
        self::assertNotSame($this->defaultResponse->github_watchers, $response->github_watchers);
        self::assertNotSame($this->defaultResponse->github_forks, $response->github_forks);
        self::assertNotSame($this->defaultResponse->github_open_issues, $response->github_open_issues);
        self::assertNotSame($this->defaultResponse->download_total, $response->download_total);
        self::assertNotSame($this->defaultResponse->download_monthly, $response->download_monthly);
        self::assertNotSame($this->defaultResponse->download_daily, $response->download_daily);
        self::assertNotSame($this->defaultResponse->favorites, $response->favorites);
    }
}
