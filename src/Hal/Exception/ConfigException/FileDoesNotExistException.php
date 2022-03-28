<?php
declare(strict_types=1);

namespace Hal\Exception\ConfigException;

use Hal\Exception\ConfigException;
use function sprintf;

/**
 * Exception thrown when the configuration defines a file or a folder to analyse that does not exist.
 */
final class FileDoesNotExistException extends ConfigException
{
    /**
     * @param string $path
     * @return FileDoesNotExistException
     */
    public static function fromConfig(string $path): FileDoesNotExistException
    {
        return new self(sprintf('Directory %s does not exist', $path));
    }
}
