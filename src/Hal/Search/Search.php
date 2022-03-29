<?php
declare(strict_types=1);

namespace Hal\Search;

use Hal\Metric\ClassMetric;
use Hal\Metric\InterfaceMetric;
use Hal\Metric\Metric;
use Hal\Metric\Registry;
use LogicException;
use function array_intersect_key;
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
     * @return array<int, Search>
     */
    public static function buildListFromArray(array $searches): array
    {
        $builder = static fn (string $name, array $search): Search => new self($name, $search);
        return array_map($builder, array_keys($searches), $searches);
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
        $config = $this->config + ['type' => '', 'nameMatches' => '', 'instanceOf' => '', 'usesClasses' => ''];

        // Check if metric object matches some special config criteria, when defined. If not, directly stops.
        $matchersCallbacks = [
            'type' => $this->matchExpectedType(...),
            'nameMatches' => $this->matchExpectedName(...),
            'instanceOf' => $this->matchInstanceOf(...),
            'usesClasses' => $this->usesClasses(...),
        ];
        foreach ($matchersCallbacks as $key => $callback) {
            if ('' !== $config[$key] && false === $callback($metric, $config[$key])) {
                return false;
            }
        }
        // Check if metric object matches some structures (ccn, lcom, mi, etc.)
        foreach (array_intersect_key($config, Registry::getDefinitions()) as $metricName => $configStructureValue) {
            if (false === $this->matchesMetric($metric->get($metricName), $configStructureValue)) {
                return false;
            }
        }

        // Store the fact the search criteria has been found in the metric.
        $config += ['failIfFound' => false];
        if (true === $config['failIfFound']) {
            $metric->set('was-not-expected', true);
            $metric->set('was-not-expected-by', [...($metric->get('was-not-expected-by') ?? []), $this->name]);
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
        foreach ($instanceOf as $expectedInterface) {
            $expectedInterface = ltrim($expectedInterface, '\\');
            if (!in_array($expectedInterface, (array)$metric->get('implements'), true)) {
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
        foreach ($usesClasses as $expectedClass) {
            foreach ((array)$metric->get('externals') as $use) {
                if (preg_match('@' . $expectedClass . '@i', $use)) {
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
        if (!preg_match_all('!^([=><]*)([\d.]+)!', $configMetricValue, $matches, PREG_SET_ORDER)) {
            throw new LogicException('Invalid search expression for key ' . $configMetricValue);
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
