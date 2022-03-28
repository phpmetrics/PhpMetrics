<?php
declare(strict_types=1);

namespace Hal\Violation\Checkers;

use Hal\Exception\ViolationsCheckerException;
use Hal\Violation\Violation;

/**
 * This class will check the violations fetched from the analysis and will consider any critical violation as
 * unacceptable.
 */
final class NoCriticalViolationsAllowed extends AbstractViolationsChecker
{
    /**
     * {@inheritDoc}
     */
    public function check(): void
    {
        $this->fillViolationsByLevels();
        // In this class, only the number of critical violations is checked.
        $nbViolations = $this->violationsByLevel[Violation::CRITICAL];
        if ($nbViolations > 0) {
            throw ViolationsCheckerException::tooManyCriticalViolations($nbViolations, 0);
        }
    }
}
