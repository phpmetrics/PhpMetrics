<?php
declare(strict_types=1);

namespace Hal\Report\Cli;

use Hal\Application\Config\ConfigBagInterface;
use Hal\Component\Output\Output;
use Hal\Metric\Metric;
use Hal\Metric\Metrics;
use Hal\Metric\SearchMetric;
use Hal\Report\ReporterInterface;
use Hal\Search\SearchInterface;
use function array_map;
use function array_splice;
use function is_array;
use function sprintf;
use const PHP_EOL;

/**
 * Reports the results of the violations defined by the "searches" in the configuration.
 */
final class SearchReporter implements ReporterInterface
{
    /**
     * @param ConfigBagInterface $config
     * @param Output $output
     */
    public function __construct(
        private readonly ConfigBagInterface $config,
        private readonly Output $output
    ) {
    }

    /**
     * @param Metrics $metrics
     */
    public function generate(Metrics $metrics): void
    {
        /** @var SearchMetric $searches */
        $searches = $metrics->get('searches');
        foreach ($searches->all() as $name => $metricsListInViolation) {
            if (!is_array($metricsListInViolation)) {
                continue;
            }
            $this->displayCliReport($name, $metricsListInViolation);
        }
    }

    /**
     * @param string $searchName
     * @param array<int, Metric> $metricsListInViolation
     * @return void
     */
    private function displayCliReport(string $searchName, array $metricsListInViolation): void
    {
        /** @var array<string, SearchInterface> $searches */
        $searches = $this->config->get('searches');
        $search = $searches[$searchName];
        $nbFound = count($metricsListInViolation);

        $tag = ([] !== $metricsListInViolation && true === $search->getConfig()['failIfFound']) ? 'error' : 'info';
        $title = sprintf('<%s>Found %d occurrences for search "%s"</%s>', $tag, $nbFound, $searchName, $tag);
        $this->output->writeln($title);

        array_map(function (Metric $metric): void {
            $this->output->writeln(sprintf('- %s', $metric->getName()));
        }, array_splice($metricsListInViolation, 0, 5));

        if ([] !== $metricsListInViolation) {
            $this->output->writeln(sprintf('… and %d more', $nbFound - 5));
        }
        $this->output->writeln(PHP_EOL);
    }
}
