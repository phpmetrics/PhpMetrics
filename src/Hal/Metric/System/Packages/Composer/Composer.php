<?php
namespace Hal\Metric\System\Packages\Composer;

use Hal\Application\Config\Config;
use Hal\Component\File\Finder;
use Hal\Metric\Metrics;
use Hal\Metric\ProjectMetric;

/**
 * Class Composer
 * @package Hal\Metric\System\Packages\Composer
 */
class Composer
{

    /**
     * @var Config
     */
    private $config;

    /**
     * GitChanges constructor.
     * @param array $files
     */
    public function __construct(Config $config, array $files)
    {
        $this->config = $config;
    }

    /**
     * @param Metrics $metrics
     * @throws ConfigException
     */
    public function calculate(Metrics $metrics)
    {

        $projectMetric = new ProjectMetric('composer');
        $projectMetric->set('packages', []);
        $metrics->attach($projectMetric);
        $packages = [];
        $rawRequirements = [];

        // find composer.json files
        $finder = new Finder(['json'], $this->config->get('exclude'));
        $files = $finder->fetch($this->config->get('files'));

        foreach ($files as $filename) {

            if (!preg_match('/composer\.json|composer-dist\.json/', $filename)) {
                continue;
            }
            $datas = (object)json_decode(file_get_contents($filename));

            if (!isset($datas->require)) {
                continue;
            }

            $rawRequirements = array_merge($rawRequirements, (array)$datas->require);
        }

        $packagist = new Packagist();
        foreach ($rawRequirements as $requirement => $version) {

            $package = $packagist->get($requirement);

            $packages[$requirement] = (object)array(
                'name' => $requirement,
                'required' => $version,
                'latest' => $package->latest,
                'license' => $package->license,
                'homepage' => $package->homepage,
                'zip' => $package->zip,
            );
        }

        $projectMetric->set('packages', $packages);
    }
}