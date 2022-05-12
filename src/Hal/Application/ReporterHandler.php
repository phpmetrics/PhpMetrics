<?php
declare(strict_types=1);

namespace Hal\Application;

use Hal\Metric\Metrics;
use Hal\Report\ReporterInterface;
use function array_map;

/**
 * This class holds a list of reporters and provide a way to request them to generate a report.
 */
final class ReporterHandler implements ReporterHandlerInterface
{
    /** @var array<int|string, ReporterInterface> */
    private readonly array $reporters;

    /**
     * @param ReporterInterface ...$reporters
     */
    public function __construct(ReporterInterface ...$reporters)
    {
        $this->reporters = $reporters;
    }

    /**
     * {@inheritDoc}
     */
    public function report(Metrics $metrics): void
    {
        array_map(static function (ReporterInterface $reporter) use ($metrics): void {
            $reporter->generate($metrics);
        }, $this->reporters);
    }
}
