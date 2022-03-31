<?php
declare(strict_types=1);

namespace Hal\Application\Config\File;

use Hal\Application\Config\ConfigBagInterface;
use Hal\Exception\ConfigException\ConfigFileReadingException;
use function is_array;
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
        $yamlData = yaml_parse_file($this->filename) ?? [];
        if (!is_array($yamlData)) {
            throw ConfigFileReadingException::inYaml($this->filename);
        }

        $this->normalizeConfig($config, $yamlData);
    }
}
