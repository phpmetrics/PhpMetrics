<?php
declare(strict_types=1);

namespace Hal\Application\Config\File;

use InvalidArgumentException;
use function is_file;
use function is_readable;
use function pathinfo;
use function sprintf;
use const PATHINFO_EXTENSION;

/**
 * Factory class to create an instance of ConfigFileReaderInterface.
 */
final class ConfigFileReaderFactory
{
    /**
     * Creates an instance of a ConfigFileReaderInterface object based on the extension of the filename
     * given in argument.
     */
    public static function createFromFileName(string $filename): ConfigFileReaderInterface
    {
        if (!is_file($filename) || !is_readable($filename)) {
            throw new InvalidArgumentException(sprintf('Cannot read configuration file "%s".', $filename));
        }

        return match (pathinfo($filename, PATHINFO_EXTENSION)) {
            'json' => new ConfigFileReaderJson($filename),
            'yaml', 'yml' => new ConfigFileReaderYaml($filename),
            'ini' => new ConfigFileReaderIni($filename),
            default => throw new InvalidArgumentException(sprintf('Unsupported config file format: "%s".', $filename)),
        };
    }
}
