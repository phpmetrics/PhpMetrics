<?php

namespace Hal\Application\Config\File;

use Hal\Application\Config\Config;

class ConfigFileReaderYaml extends ConfigFileReaderJson implements ConfigFileReaderInterface
{
    /**
     * @param Config $config
     */
    public function read(Config $config)
    {
        if (!function_exists('yaml_parse')) {
            throw new \RuntimeException('YAML parser not found. Please install the PECL extension "yaml".');
        }

        $json = yaml_parse_file($this->filename);

        $this->parseJson($json, $config);
    }
}
