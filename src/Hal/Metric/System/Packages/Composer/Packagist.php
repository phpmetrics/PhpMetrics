<?php
namespace Hal\Metric\System\Packages\Composer;

/**
 * @package Hal\Metric\System\Packages\Composer
 */
class Packagist
{

    /**
     * @param $package
     * @return \StdClass
     */
    public function get($package)
    {
        $response = new \StdClass;
        $response->latest = null;
        $response->license = [];
        $response->homepage = null;
        $response->description = null;
        $response->time = null;
        $response->zip = null;
        $response->compare = null;
        $response->type = 'unknown';
        $response->github_stars = 0;
        $response->github_watchers = 0;
        $response->github_forks = 0;
        $response->github_open_issues = 0;
        $response->download_total = 0;
        $response->download_monthly = 0;
        $response->download_daily = 0;
        $response->favers = 0;

        if (!preg_match('/\w+\/\w+/', $package)) {
            return $response;
        }
        list($user, $name) = explode('/', $package);
        $uri = sprintf('https://packagist.org/packages/%s/%s.json', $user, $name);
        $json = json_decode(@file_get_contents($uri));

        if (!isset($json->package) || !is_object($json->package)) {
            return $response;
        }

        $response->type = $json->package->type;
        $response->description = $json->package->description;
        $response->type = $json->package->type;
        $response->github_stars = $json->package->github_stars;
        $response->github_watchers = $json->package->github_watchers;
        $response->github_forks = $json->package->github_forks;
        $response->github_open_issues = $json->package->github_open_issues;
        $response->download_total = $json->package->downloads->total;
        $response->download_monthly = $json->package->downloads->monthly;
        $response->download_daily = $json->package->downloads->daily;
        $response->favers = $json->package->favers;

        // get latest version
        $latest = '0.0.0';
        foreach ((array)$json->package->versions as $version => $datas) {
            if ($version[0] === 'v') {
                $version = substr($version, 1);
            }
            if (!preg_match('#^[\.\d]+$#', $version)) {
                continue;
            }
            if ($compare = version_compare($version, $latest) == 1) {
                $latest = $version;
                $response->name = $package;
                $response->latest = $version;
                $response->license = (array)$datas->license;
                $response->homepage = $datas->homepage;
                $response->time = $datas->time;
                $response->zip = $datas->dist->url;
                $response->compare = $compare;
            }
        }

        return $response;
    }
}
