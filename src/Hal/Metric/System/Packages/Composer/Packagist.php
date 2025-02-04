<?php
declare(strict_types=1);

namespace Hal\Metric\System\Packages\Composer;

use Hal\Component\File\ReaderInterface;
use stdClass;
use function ltrim;
use function preg_match;
use function sprintf;
use function version_compare;

/**
 * Responsible for the fetching of the information related to external dependencies using Packagist as a registry.
 */
final class Packagist implements ComposerRegistryConnectorInterface
{
    public function __construct(private readonly ReaderInterface $fileReader)
    {
    }

    /**
     * {@inheritDoc}
     */
    public function get(string $package): stdClass
    {
        $response = [
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

        if (0 === preg_match('/\w+\/\w+/', $package)) {
            return (object)$response;
        }
        $json = $this->fileReader->httpReadJson(sprintf('https://packagist.org/packages/%s.json', $package));

        if (!isset($json->package)) {
            return (object)$response;
        }

        $response['type'] = $json->package->type;
        $response['description'] = $json->package->description;
        $response['github_stars'] = $json->package->github_stars;
        $response['github_watchers'] = $json->package->github_watchers;
        $response['github_forks'] = $json->package->github_forks;
        $response['github_open_issues'] = $json->package->github_open_issues;
        $response['download_total'] = $json->package->downloads->total;
        $response['download_monthly'] = $json->package->downloads->monthly;
        $response['download_daily'] = $json->package->downloads->daily;
        $response['favorites'] = $json->package->favers;

        // get latest version
        $latest = '0.0.0';

        foreach ((array)$json->package->versions as $version => $packageDataAtSpecificVersion) {
            $version = ltrim((string)$version, 'v');
            if (0 === preg_match('#^(\d|\.)+$#', $version) || version_compare($version, $latest, '<')) {
                continue;
            }

            $latest = $version;
            $response['name'] = $package;
            $response['latest'] = $version;
            $response['license'] = (array)$packageDataAtSpecificVersion->license;
            $response['homepage'] = $packageDataAtSpecificVersion->homepage;
            $response['time'] = $packageDataAtSpecificVersion->time;
            $response['zip'] = $packageDataAtSpecificVersion->dist->url;
        }

        return (object)$response;
    }
}
