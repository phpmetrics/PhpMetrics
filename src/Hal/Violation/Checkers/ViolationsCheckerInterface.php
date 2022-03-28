<?php
declare(strict_types=1);

namespace Hal\Violation\Checkers;

use Hal\Exception\ViolationsCheckerException;

/**
 * Provides a way to check all the violations, and according to specific criteria (number of violations, level too high,
 * etc.), will differentiate from acceptable or not.
 */
interface ViolationsCheckerInterface
{
    /**
     * Ensure the violations are not too bad according to expected. Throws a dedicated exception if the violations are
     * not acceptable.
     *
     * @throws ViolationsCheckerException When the violations are so bad that they are considered unacceptable.
     * @return void
     */
    public function check(): void;
}
