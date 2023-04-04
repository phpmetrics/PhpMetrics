<?php
declare(strict_types=1);

namespace Hal\Application\Config\File;

use Hal\Application\Config\ConfigBagInterface;
use Hal\Exception\ConfigException\ConfigFileReadingException;

/**
 * Reader of a .ini configuration file.
 */
final class ConfigFileReaderIni extends AbstractConfigFileReader
{
    /**
     * {@inheritDoc}
     */
    public function read(ConfigBagInterface $config): void
    {
        $options = $this->fileReader->readIni($this->filename);

        if (false === $options) {
            throw ConfigFileReadingException::inIni($this->filename);
        }

        $this->normalizeConfig($config, $options);
    }
}
