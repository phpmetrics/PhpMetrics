<?php
declare(strict_types=1);

namespace Tests\Hal\Metric\Searches;

use Hal\Metric\Metric;
use Hal\Metric\Metrics;
use Hal\Metric\Searches\Searches;
use Hal\Metric\SearchMetric;
use Hal\Search\SearchInterface;
use Phake;
use PHPUnit\Framework\TestCase;

final class SearchesTest extends TestCase
{
    public function testICanCalculateSearchesOnMetrics(): void
    {
        $metrics = Phake::mock(Metrics::class);
        $metricsList = [
            Phake::mock(Metric::class),
            Phake::mock(Metric::class),
        ];
        Phake::when($metrics)->__call('all', [])->thenReturn($metricsList);
        $searchMetricCollector = null;
        Phake::when($metrics)->__call('attach', [Phake::anyParameters()])->thenReturnCallback(
            static function (SearchMetric $searchMetric) use (&$searchMetricCollector): void {
                $searchMetricCollector = $searchMetric;
            }
        );

        $configSearches = [
            Phake::mock(SearchInterface::class),
            Phake::mock(SearchInterface::class),
            Phake::mock(SearchInterface::class),
        ];
        Phake::when($configSearches[0])->__call('getName', [])->thenReturn('Criteria A');
        Phake::when($configSearches[0])->__call('matches', [$metricsList[0]])->thenReturn(true);
        Phake::when($configSearches[0])->__call('matches', [$metricsList[1]])->thenReturn(true);
        Phake::when($configSearches[1])->__call('getName', [])->thenReturn('Criteria B');
        Phake::when($configSearches[1])->__call('matches', [$metricsList[0]])->thenReturn(false);
        Phake::when($configSearches[1])->__call('matches', [$metricsList[1]])->thenReturn(true);
        Phake::when($configSearches[2])->__call('getName', [])->thenReturn('Criteria C');
        Phake::when($configSearches[2])->__call('matches', [$metricsList[0]])->thenReturn(false);
        Phake::when($configSearches[2])->__call('matches', [$metricsList[1]])->thenReturn(false);

        $searches = new Searches($metrics, $configSearches);
        $searches->calculate();

        foreach ($configSearches as $configSearch) {
            Phake::verify($configSearch)->__call('getName', []);
            foreach ($metricsList as $metric) {
                Phake::verify($configSearch)->__call('matches', [$metric]);
            }
            Phake::verifyNoOtherInteractions($configSearch);
        }
        Phake::verify($metrics, Phake::times(count($configSearches)))->__call('all', []);
        Phake::verify($metrics)->__call('attach', [$searchMetricCollector]);

        /** @var SearchMetric $searchMetricCollector */
        self::assertSame([$metricsList[0], $metricsList[1]], $searchMetricCollector->get('Criteria A'));
        self::assertSame([1 => $metricsList[1]], $searchMetricCollector->get('Criteria B'));
        self::assertSame([], $searchMetricCollector->get('Criteria C'));
    }
}
