<?php
declare(strict_types=1);

namespace Hal\Application\Config\File;

use Hal\Application\Config\ConfigBagInterface;
use Hal\Exception\ConfigException\ConfigFileReadingException;
use function yaml_parse_file;

/**
 * Reader of a .yaml or .yml configuration file.
 */
final class ConfigFileReaderYaml extends AbstractConfigFileReader
{
    /**
     * {@inheritDoc}
     */
    public function read(ConfigBagInterface $config): void
    {
        $yamlData = yaml_parse_file($this->filename);
        if (false === $yamlData) {
            throw ConfigFileReadingException::inYaml($this->filename);
        }

        $this->normalizeConfig($config, yaml_parse_file($this->filename));
    }
}
