<?php
declare(strict_types=1);

namespace Hal\Exception;

use RuntimeException;

/**
 * Exception thrown when OpenMetrics-PHP Exposition Text is not installed.
 */
final class NotInstalledOpenMetricsPhpExpositionTextException extends RuntimeException
{
    /**
     * @return NotInstalledOpenMetricsPhpExpositionTextException
     */
    public static function notInstalled(): NotInstalledOpenMetricsPhpExpositionTextException
    {
        return new self('Please install OpenMetrics-PHP Exposition Text to use --report-openmetrics. Try running "composer require openmetrics-php/exposition-text".');
    }
}
