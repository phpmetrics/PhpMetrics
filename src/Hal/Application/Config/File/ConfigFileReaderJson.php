<?php
declare(strict_types=1);

namespace Hal\Application\Config\File;

use Hal\Application\Config\ConfigBagInterface;
use Hal\Exception\ConfigException\ConfigFileReadingException;

/**
 * Reader of a .json configuration file.
 */
final class ConfigFileReaderJson extends AbstractConfigFileReader
{
    /**
     * {@inheritDoc}
     */
    public function read(ConfigBagInterface $config): void
    {
        /** @var false|array<string, mixed> $jsonData */
        $jsonData = $this->fileReader->readJson($this->filename);

        if (false === $jsonData) {
            throw ConfigFileReadingException::inJson($this->filename);
        }

        $this->normalizeConfig($config, $jsonData);
    }
}
