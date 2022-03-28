<?php
declare(strict_types=1);

namespace Hal\Exception\ConfigException;

use Hal\Exception\ConfigException;
use function sprintf;

/**
 * Exception thrown when the configuration file can not be read from the expected format.
 */
final class ConfigFileReadingException extends ConfigException
{
    /**
     * @param string $path
     * @return ConfigFileReadingException
     */
    public static function inJson(string $path): ConfigFileReadingException
    {
        return new self(sprintf('Cannot read JSON configuration file "%s".', $path));
    }

    /**
     * @param string $path
     * @return ConfigFileReadingException
     */
    public static function inYaml(string $path): ConfigFileReadingException
    {
        return new self(sprintf('Cannot read YAML configuration file "%s".', $path));
    }

    /**
     * @param string $path
     * @return ConfigFileReadingException
     */
    public static function inIni(string $path): ConfigFileReadingException
    {
        return new self(sprintf('Cannot read INI configuration file "%s".', $path));
    }
}
