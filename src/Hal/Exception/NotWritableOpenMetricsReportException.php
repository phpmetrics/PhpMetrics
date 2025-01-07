<?php
declare(strict_types=1);

namespace Hal\Exception;

use RuntimeException;
use function sprintf;

/**
 * Exception thrown when trying to write an OpenMetrics report but user has no permissions to do so.
 */
final class NotWritableOpenMetricsReportException extends RuntimeException
{
    /**
     * @param string $logFile
     * @return NotWritableOpenMetricsReportException
     */
    public static function noPermission(string $logFile): NotWritableOpenMetricsReportException
    {
        return new self(sprintf('You do not have permissions to write OpenMetrics report in %s', $logFile));
    }
}
