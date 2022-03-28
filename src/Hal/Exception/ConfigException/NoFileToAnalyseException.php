<?php
declare(strict_types=1);

namespace Hal\Exception\ConfigException;

use Hal\Exception\ConfigException;

/**
 * Exception thrown when the user is running PhpMetrics without setting any file to analyse.
 */
final class NoFileToAnalyseException extends ConfigException
{
    /**
     * @return NoFileToAnalyseException
     */
    public static function configHasNoFilesSet(): NoFileToAnalyseException
    {
        return new self('Directory to parse is missing or incorrect');
    }
}
