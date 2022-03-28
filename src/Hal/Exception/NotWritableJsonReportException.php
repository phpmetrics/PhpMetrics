<?php
declare(strict_types=1);

namespace Hal\Exception;

use RuntimeException;
use function sprintf;

/**
 * Exception thrown when trying to write a JSON report but user has no permissions to do so.
 */
final class NotWritableJsonReportException extends RuntimeException
{
    /**
     * @param string $logFile
     * @return NotWritableJsonReportException
     */
    public static function noPermission(string $logFile): NotWritableJsonReportException
    {
        return new self(sprintf('You do not have permissions to write JSON report in %s', $logFile));
    }
}
