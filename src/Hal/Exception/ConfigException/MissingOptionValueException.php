<?php
declare(strict_types=1);

namespace Hal\Exception\ConfigException;

use Hal\Exception\ConfigException;
use function sprintf;

/**
 * Exception thrown when a configuration requires a value but the user did not provide any.
 */
final class MissingOptionValueException extends ConfigException
{
    /**
     * @param string $optionName
     * @return MissingOptionValueException
     */
    public static function requireValue(string $optionName): MissingOptionValueException
    {
        return new self(sprintf('%s option requires a value', $optionName));
    }
}
