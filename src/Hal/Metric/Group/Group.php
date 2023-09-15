<?php
declare(strict_types=1);

namespace Hal\Metric\Group;

use Hal\Metric\Metric;
use Hal\Metric\Metrics;
use function array_map;
use function preg_match;

/**
 * Class Group
 * Allows grouping metrics by regex, given them a name
 */
final class Group implements GroupInterface
{
    /**
     * Group constructor.
     *
     * @param string $name
     * @param non-empty-string $regex
     */
    public function __construct(
        public readonly string $name,
        private readonly string $regex
    ) {
    }

    /**
     * {@inheritDoc}
     */
    public function getRegex(): string
    {
        return $this->regex;
    }

    /**
     * {@inheritDoc}
     */
    public function reduceMetrics(Metrics $metrics): Metrics
    {
        $matched = new Metrics();
        array_map(function (Metric $metric) use ($matched): void {
            if (1 !== preg_match($this->getRegex(), $metric->getName())) {
                return;
            }
            $matched->attach($metric);
        }, $metrics->all());
        return $matched;
    }
}
