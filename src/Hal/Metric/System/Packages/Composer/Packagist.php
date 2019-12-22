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
        $response->time = null;
        $response->zip = null;
        $response->compare = null;

        if (!preg_match('/\w+\/\w+/', $package)) {
            return $response;
        }
        list($user, $name) = explode('/', $package);
        $uri = sprintf('https://packagist.org/packages/%s/%s.json', $user, $name);
        $json = json_decode(@file_get_contents($uri));

        if (!isset($json->package) || !is_object($json->package)) {
            return $response;
        }

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
