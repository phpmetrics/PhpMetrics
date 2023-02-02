<?php
declare(strict_types=1);

namespace Hal\Metric\Searches;

use Hal\Metric\CalculableInterface;
use Hal\Metric\Metric;
use Hal\Metric\Metrics;
use Hal\Metric\SearchMetric;
use Hal\Search\SearchInterface;
use function array_filter;
use function array_map;

/**
 * This class is in charge of the confrontation between the search criteria defined in the configuration and the data
 * stored in the metrics from the analysis. When a metric is matching a criterion, it is stored into a SearchMetric
 * object.
 */
final class Searches implements CalculableInterface
{
    /**
     * @param Metrics $metrics
     * @param array<int, SearchInterface> $configSearches
     */
    public function __construct(
        private readonly Metrics $metrics,
        private readonly array $configSearches
    ) {
    }

    /**
     * {@inheritDoc}
     */
    public function calculate(): void
    {
        $foundSearch = new SearchMetric('searches');
        array_map(function (SearchInterface $search) use ($foundSearch): void {
            $foundSearch->set($search->getName(), $this->findMatchingPattern($search, $this->metrics));
        }, $this->configSearches);

        $this->metrics->attach($foundSearch);
    }

    /**
     * Returns the list of metrics that are satisfying the search criterion.
     *
     * @param SearchInterface $search The search criterion
     * @param Metrics $metrics Metrics handler.
     * @return array<string, Metric>
     */
    private function findMatchingPattern(SearchInterface $search, Metrics $metrics): array
    {
        return array_filter($metrics->all(), static fn (Metric $metric): bool => $search->matches($metric));
    }
}
