<?php
declare(strict_types=1);

namespace Hal\Exception;

use RuntimeException;
use function sprintf;

/**
 * Exception thrown when trying to write a CSV report but user has no permissions to do so.
 */
final class NotWritableCsvReportException extends RuntimeException
{
    /**
     * @param string $logFile
     * @return NotWritableCsvReportException
     */
    public static function noPermission(string $logFile): NotWritableCsvReportException
    {
        return new self(sprintf('You do not have permissions to write CSV report in %s', $logFile));
    }
}
