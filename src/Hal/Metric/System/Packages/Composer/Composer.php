<?php
namespace Hal\Metric\System\Packages\Composer;

use Hal\Application\Config\Config;
use Hal\Application\Config\ConfigException;
use Hal\Component\File\Finder;
use Hal\Metric\Metrics;
use Hal\Metric\ProjectMetric;

/**
 * @package Hal\Metric\System\Packages\Composer
 */
class Composer
{

    /**
     * @var Config
     */
    private $config;

    /**
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
        $rawRequirements = $this->getComposerJsonRequirements();
        $rawInstalled = $this->getComposerLockInstalled(\array_keys($rawRequirements));

        $packagist = new Packagist();
        foreach ($rawRequirements as $requirement => $version) {
            $package = $packagist->get($requirement);

            $packages[$requirement] = (object)[
                'name' => $requirement,
                'required' => $version,
                'installed' => isset($rawInstalled[$requirement]) ? $rawInstalled[$requirement] : null,
                'latest' => $package->latest,
                'license' => $package->license,
                'homepage' => $package->homepage,
                'zip' => $package->zip,
            ];
        }

        $projectMetric->set('packages', $packages);
        $projectMetric->set('packages-installed', $rawInstalled);
    }

    /**
     * Returns the requirements defined in the composer(-dist)?.json file.
     * @return array
     */
    protected function getComposerJsonRequirements()
    {
        $rawRequirements = [[]];

        // find composer.json files
        $finder = new Finder(['json'], $this->config->get('exclude'));
        $files = $finder->fetch($this->config->get('files'));

        foreach ($files as $filename) {
            if (!\preg_match('/composer(-dist)?\.json/', $filename)) {
                continue;
            }
            $composerJson = (object)\json_decode(\file_get_contents($filename));

            if (!isset($composerJson->require)) {
                continue;
            }

            $rawRequirements[] = (array)$composerJson->require;
        }

        return \call_user_func_array('array_merge', $rawRequirements);
    }

    /**
     * Returns the installed packages from the composer.lock file.
     * @param array $rootPackageRequirements List of requirements to match installed packages only with requirements.
     * @return array
     */
    protected function getComposerLockInstalled($rootPackageRequirements)
    {
        $rawInstalled = [[]];

        // Find composer.lock file
        $finder = new Finder(['lock'], $this->config->get('exclude'));
        $files = $finder->fetch($this->config->get('files'));

        // List all composer.lock found in the project.
        foreach ($files as $filename) {
            if (false === \strpos($filename, 'composer.lock')) {
                continue;
            }
            $composerLockJson = (object)\json_decode(\file_get_contents($filename));

            if (!isset($composerLockJson->packages)) {
                continue;
            }

            $installed = [];
            foreach ($composerLockJson->packages as $package) {
                if (!\in_array($package->name, $rootPackageRequirements, true)) {
                    continue;
                }

                $installed[$package->name] = \preg_replace('#[^.\d]#', '', $package->version);
            }

            $rawInstalled[] = $installed;
        }

        return \call_user_func_array('array_merge', $rawInstalled);
    }
}
