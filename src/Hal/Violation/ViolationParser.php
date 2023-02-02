<?php
declare(strict_types=1);

namespace Hal\Violation;

use Hal\Metric\Metric;
use Hal\Metric\Metrics;
use function array_map;

/**
 * This class is responsible for the application of all violations given to all metrics.
 * By doing so, every metrics will have a ViolationHandlerInterface object which will hold all related violations.
 */
final class ViolationParser implements ViolationParserInterface
{
    /** @var array<Violation> */
    private readonly array $violationsChecker;

    /**
     * @param Violation ...$violation
     */
    public function __construct(Violation ...$violation)
    {
        $this->violationsChecker = $violation;
    }

    /**
     * {@inheritDoc}
     */
    public function apply(Metrics $metrics): void
    {
        array_map(function (Metric $metric): void {
            $metric->set('violations', new ViolationsHandler());

            array_map(static function (Violation $violation) use ($metric): void {
                $violation->apply($metric);
            }, $this->violationsChecker);
        }, $metrics->all());
    }
}
