<?php
declare(strict_types=1);

namespace Hal\Application\Config\File;

use Hal\Application\Config\ConfigBagInterface;
use Hal\Exception\ConfigException\ConfigFileReadingException;
use function parse_ini_file;

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
        $options = parse_ini_file($this->filename, true);

        if (false === $options) {
            throw ConfigFileReadingException::inIni($this->filename);
        }

        $this->normalizeConfig($config, $options);
    }
}
