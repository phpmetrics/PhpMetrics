<?php
declare(strict_types=1);

namespace Hal\Application\Config\File;

use Hal\Application\Config\ConfigBagInterface;
use Hal\Exception\ConfigException\ConfigFileReadingException;
use function is_array;

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
        $yamlData = $this->fileReader->readYaml($this->filename);

        if (!is_array($yamlData)) {
            throw ConfigFileReadingException::inYaml($this->filename);
        }

        $this->normalizeConfig($config, $yamlData);
    }
}
