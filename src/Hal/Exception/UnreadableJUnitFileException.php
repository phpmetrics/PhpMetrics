<?php
declare(strict_types=1);

namespace Hal\Exception;

use RuntimeException;
use function sprintf;

/**
 * Exception thrown when trying to read the JUnit file to calculate metrics on unit tests but the report is not
 * readable.
 */
final class UnreadableJUnitFileException extends RuntimeException
{
    /**
     * @param string $junitFilename
     * @return UnreadableJUnitFileException
     */
    public static function noPermission(string $junitFilename): UnreadableJUnitFileException
    {
        return new self(sprintf('JUnit report "%s" cannot be read.', $junitFilename));
    }
}
