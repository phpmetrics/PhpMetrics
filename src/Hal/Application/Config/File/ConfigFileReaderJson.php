<?php

namespace Hal\Application\Config\File;

use Hal\Application\Config\Config;
use Hal\Search\SearchesFactory;
use InvalidArgumentException;

class ConfigFileReaderJson implements ConfigFileReaderInterface
{
    /**
     * @var string
     */
    protected $filename;

    /**
     * @param string $filename
     */
    public function __construct($filename)
    {
        $this->filename = $filename;
    }

    /**
     * @param Config $config
     *
     * @return void
     */
    public function read(Config $config)
    {
        $jsonText = file_get_contents($this->filename);

        if (false === $jsonText) {
            throw new InvalidArgumentException("Cannot read configuration file '{$this->filename}'");
        }

        $jsonData = json_decode($jsonText, true);

        $this->parseJson($jsonData, $config);
    }

    /**
     * @return string
     */
    private function resolvePath($path)
    {
        if (DIRECTORY_SEPARATOR !== $path[0]) {
            $path = dirname($this->filename) . DIRECTORY_SEPARATOR . $path;
        }

        return $path;
    }

    protected function parseJson($jsonData, Config $config)
    {
        if (false === $jsonData || null === $jsonData) {
            throw new InvalidArgumentException("Bad config file '{$this->filename}'");
        }

        if (isset($jsonData['includes'])) {
            $includes = $jsonData['includes'];
            $files = [];
            // with config file, includes are relative to config file
            foreach ($includes as $include) {
                $include = $this->resolvePath($include);
                $files[] = $include;
            }
            $config->set('files', $files);
        }

        if (isset($jsonData['groups'])) {
            $config->set('groups', $jsonData['groups']);
        }

        if (isset($jsonData['extensions'])) {
            $config->set('extensions', implode(',', $jsonData['extensions']));
        }

        // Composer
        if (array_key_exists('composer', $jsonData)) {
            $config->set('composer', (bool) $jsonData['composer']);
        }

        // Search
        if (!isset($jsonData['searches'])) {
            $jsonData['searches'] = [];
        }
        $factory = new SearchesFactory();
        $config->set('searches', $factory->factory($jsonData['searches']));

        if (isset($jsonData['excludes'])) {
            // retro-compatibility
            // "exclude" is a string
            // excludes is an array
            $config->set('exclude', implode(',', $jsonData['excludes']));
        } else {
            if (isset($jsonData['exclude'])) {
                $config->set('exclude', $jsonData['exclude']);
            }
        }

        if (isset($jsonData['plugins']['git']['binary'])) {
            $config->set('git', $jsonData['plugins']['git']['binary']);
        }

        // backward compatibility with typo in documentation
        // see https://github.com/phpmetrics/PhpMetrics/issues/441
        // file -> report
        if (isset($jsonData['plugins']['junit']['file'])) {
            $jsonData['plugins']['junit']['report'] = $jsonData['plugins']['junit']['file'];
        }

        if (isset($jsonData['plugins']['junit']['report'])) {
            $config->set('junit', $jsonData['plugins']['junit']['report']);
        }

        // reports
        if (isset($jsonData['report']) && is_array($jsonData['report'])) {
            foreach ($jsonData['report'] as $reportType => $path) {
                $path = $this->resolvePath($path);
                $config->set('report-' . $reportType, $path);
            }
        }
    }
}
