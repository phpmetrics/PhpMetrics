<?php
declare(strict_types=1);

namespace Hal\Violation\Checkers;

use Hal\Metric\Metrics;
use Hal\Violation\Violation;
use function array_fill_keys;
use function array_map;

/**
 * Abstract class that will give the keys to all concrete checkers in order to let them just do the check with all
 * calculations already set here.
 */
abstract class AbstractViolationsChecker implements ViolationsCheckerInterface
{
    /** @var array<int, int> Number of violations by level. */
    protected array $violationsByLevel;

    final public function __construct(private readonly Metrics $metrics)
    {
        $levels = [Violation::INFO, Violation::WARNING, Violation::ERROR, Violation::CRITICAL];
        $this->violationsByLevel = array_fill_keys($levels, 0);
    }

    /**
     * Fills the array with the number of violations by levels using the metrics.
     *
     * @return void
     */
    final protected function fillViolationsByLevels(): void
    {
        foreach ($this->metrics->all() as $metric) {
            array_map(function (Violation $violation): void {
                ++$this->violationsByLevel[$violation->getLevel()];
            }, $metric->get('violations'));
        }
    }
}
