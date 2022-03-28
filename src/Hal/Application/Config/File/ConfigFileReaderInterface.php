<?php
declare(strict_types=1);

namespace Hal\Application\Config\File;

use Hal\Application\Config\ConfigBagInterface;

/**
 * Defines all actions a specific ConfigFileReader object must provide.
 * Only action allowed is to read a configuration file to set all properties in a configuration object.
 */
interface ConfigFileReaderInterface
{
    /**
     * Reads the configuration from a file and hydrates the configuration object given in argument.
     *
     * @param ConfigBagInterface $config The configuration object to set with the data read from a configuration file.
     */
    public function read(ConfigBagInterface $config): void;
}
