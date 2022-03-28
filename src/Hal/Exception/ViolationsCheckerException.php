<?php
declare(strict_types=1);

namespace Hal\Exception;

use RuntimeException;
use function sprintf;

/**
 * This kind of exception is thrown when a violation or a group of violations, based on their numbers or levels, are
 * considered unacceptable.
 */
final class ViolationsCheckerException extends RuntimeException
{
    /**
     * @param int $nbViolations
     * @param int $nbTolerated
     * @return ViolationsCheckerException
     */
    public static function tooManyCriticalViolations(int $nbViolations, int $nbTolerated): ViolationsCheckerException
    {
        $msg = 'Failure due to too many critical violations: got %d while %d tolerated.';
        return new self(sprintf($msg, $nbViolations, $nbTolerated));
    }
}
