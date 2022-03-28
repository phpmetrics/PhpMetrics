<?php
declare(strict_types=1);

namespace Hal\Metric\System\Packages\Composer;

use JsonException;
use stdClass;
use function file_get_contents;
use function json_decode;
use function ltrim;
use function preg_match;
use function sprintf;
use function str_replace;
use function stream_context_create;
use function version_compare;
use const JSON_THROW_ON_ERROR;

/**
 * Responsible for the fetching of the information related to external dependencies using Packagist as a registry.
 */
final class Packagist implements ComposerRegistryConnectorInterface
{
    /**
     * {@inheritDoc}
     * @throws JsonException
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

        if (!preg_match('/\w+\/\w+/', $package)) {
            return (object)$response;
        }
        $json = $this->getURIContentAsJson(sprintf('https://packagist.org/packages/%s.json', $package));

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
            $version = ltrim($version, 'v');
            if (!preg_match('#^(\d|\.)+$#', $version) || version_compare($version, $latest, '<')) {
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

    /**
     * Download the given URI and decode it as JSON.
     *
     * @param string $uri
     *
     * @return stdClass
     * @throws JsonException When the content of the request is not possible to decode the response in JSON.
     */
    private function getURIContentAsJson(string $uri): stdClass
    {
        $_SERVER += ['HTTP_PROXY' => ''];
        $httpOptions = ['ignore_errors' => true];
        if ('' !== $_SERVER['HTTP_PROXY']) {
            $httpOptions += [
                'proxy' => str_replace(['http://', 'https://'], 'tcp://', $_SERVER['HTTP_PROXY']),
                'request_fulluri' => true,
            ];
        }
        $jsonContent = file_get_contents($uri, context: stream_context_create(['http' => $httpOptions]));
        if (false === $jsonContent) {
            return (object)[];
        }

        /**
         * @noinspection JsonEncodingApiUsageInspection TODO: Wait for a fix
         * @see https://github.com/kalessil/phpinspectionsea/issues/1725
         */
        return json_decode($jsonContent, flags: JSON_THROW_ON_ERROR);
    }
}
