<?php
declare(strict_types=1);

namespace Hal\Search;

use Hal\Exception\ConfigException\SearchValidationException;
use Hal\Metric\ClassMetric;
use Hal\Metric\InterfaceMetric;
use Hal\Metric\Metric;
use Hal\Metric\Registry;
use function array_combine;
use function array_filter;
use function array_intersect_key;
use function array_key_exists;
use function array_keys;
use function array_map;
use function in_array;
use function ltrim;
use function preg_match;
use function preg_match_all;
use const PREG_SET_ORDER;

/**
 * Class that represent a search criterion defined by configuration. Metrics will be confronted against this search to
 * detect if this causes violations.
 */
final class Search implements SearchInterface
{
    /**
     * @param string $name
     * @param array<string, mixed> $config
     */
    private function __construct(
        private readonly string $name,
        private readonly array $config
    ) {
    }

    /**
     * Builds a list of Search object from the array given in argument.
     *
     * @param array<string, array<string, mixed>> $searches
     * @return array<string, Search>
     */
    public static function buildListFromArray(array $searches): array
    {
        $builder = static fn (string $name, array $search): Search => new self($name, $search);
        $searchesNames = array_keys($searches);
        return array_combine($searchesNames, array_map($builder, $searchesNames, $searches));
    }

    /**
     * {@inheritDoc}
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * {@inheritDoc}
     */
    public function getConfig(): array
    {
        return $this->config;
    }

    /**
     * {@inheritDoc}
     */
    public function matches(Metric $metric): bool
    {
        $config = $this->config + ['type' => '', 'nameMatches' => '', 'instanceOf' => [], 'usesClasses' => []];
        $matchersCallbacks = [
            'type' => $this->matchExpectedType(...),
            'nameMatches' => $this->matchExpectedName(...),
            'instanceOf' => $this->matchInstanceOf(...),
            'usesClasses' => $this->usesClasses(...),
        ];
        $matchersStructures = Registry::getDefinitions();
        // This array is a sample of all valid and not empty configurations that can match metric.
        /** @var array<string>|array<array<string>> $matchableConfig */
        $matchableConfig = array_filter(array_intersect_key($config, [...$matchersCallbacks, ...$matchersStructures]));

        // If there are no matchable metrics, nothing can match.
        if ([] === $matchableConfig) {
            return false;
        }
        // Check if metric object matches some special config criteria, when defined. If not, directly stops.
        foreach ($matchersCallbacks as $key => $callback) {
            if (array_key_exists($key, $matchableConfig) && false === $callback($metric, $matchableConfig[$key])) {
                return false;
            }
        }
        // Check if metric object matches some structures (ccn, lcom, mi, etc.). If not, directly stops.
        foreach (array_intersect_key($matchableConfig, $matchersStructures) as $metricName => $configStructureValue) {
            if (false === $this->matchesMetric($metric->get($metricName), $configStructureValue)) {
                return false;
            }
        }

        // At this point, there are at least 1 match.

        // Store the fact the search criteria has been found in the metric.
        $config += ['failIfFound' => false];
        if (true === $config['failIfFound']) {
            $metric->set('was-not-expected', true);
            /** @var array<string> $wasNotExpectedBy */
            $wasNotExpectedBy = $metric->get('was-not-expected-by') ?? [];
            $metric->set('was-not-expected-by', [...$wasNotExpectedBy, $this->name]);
        }
        return true;
    }

    /**
     * @param Metric $metric
     * @param string $expectedType
     * @return bool
     */
    private function matchExpectedType(Metric $metric, string $expectedType): bool
    {
        return match ($expectedType) {
            'class' => $metric instanceof ClassMetric && !$metric instanceof InterfaceMetric,
            'interface' => $metric instanceof InterfaceMetric,
            default => false,
        };
    }

    /**
     * @param Metric $metric
     * @param string $expectedName
     * @return bool
     */
    private function matchExpectedName(Metric $metric, string $expectedName): bool
    {
        return (bool)preg_match('@' . $expectedName . '@i', $metric->getName());
    }

    /**
     * @param Metric $metric
     * @param array<string> $instanceOf
     * @return bool
     */
    private function matchInstanceOf(Metric $metric, array $instanceOf): bool
    {
        $implements = (array)$metric->get('implements');
        foreach ($instanceOf as $expectedInterface) {
            $expectedInterface = ltrim($expectedInterface, '\\');
            if (!in_array($expectedInterface, $implements, true)) {
                return false;
            }
        }

        return true;
    }

    /**
     * @param Metric $metric
     * @param array<string> $usesClasses
     * @return bool
     */
    private function usesClasses(Metric $metric, array $usesClasses): bool
    {
        /** @var array<string> $externals */
        $externals = (array)$metric->get('externals');
        foreach ($usesClasses as $expectedClass) {
            foreach ($externals as $use) {
                if (1 === preg_match('@' . $expectedClass . '@i', $use)) {
                    return true;
                }
            }
        }

        return false;
    }

    /**
     * @param mixed $metricValue
     * @param string $configMetricValue
     * @return bool
     */
    private function matchesMetric(mixed $metricValue, string $configMetricValue): bool
    {
        // TODO: This "if" should probably be duplicated to the SearchesValidator.php file to throw exception earlier
        //       in the process if there is an invalid value for the custom metric.
        if (0 === preg_match_all('!^([=><]*)([\d.]+)!', $configMetricValue, $matches, PREG_SET_ORDER)) {
            throw SearchValidationException::invalidCustomMetricComparison($configMetricValue);
        }
        [, $operator, $expected] = $matches[0];

        /** @noinspection TypeUnsafeComparisonInspection Expected to be loosely typed in this case. */
        return match ($operator) {
            '=', '' => $metricValue == $expected,
            '>' => $metricValue > $expected,
            '<' => $metricValue < $expected,
            '>=', '=>' => $metricValue >= $expected,
            '<=', '=<' => $metricValue <= $expected,
            default => false,
        };
    }
}
