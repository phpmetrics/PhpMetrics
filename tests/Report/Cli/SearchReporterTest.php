<?php
declare(strict_types=1);

namespace Tests\Hal\Report\Cli;

use Hal\Application\Config\ConfigBagInterface;
use Hal\Component\Output\Output;
use Hal\Metric\Metric;
use Hal\Metric\Metrics;
use Hal\Metric\SearchMetric;
use Hal\Report\Cli\SearchReporter;
use Hal\Search\SearchInterface;
use Phake;
use PHPUnit\Framework\TestCase;
use const PHP_EOL;

final class SearchReporterTest extends TestCase
{
    public function testCliSearchReportIsOK(): void
    {
        $metrics = Phake::mock(Metrics::class);
        $config = Phake::mock(ConfigBagInterface::class);
        $output = Phake::mock(Output::class);
        $searchMetric = Phake::mock(SearchMetric::class);

        $allMetricsInViolations = [
            'ignored' => 'This is not an array',
            'Long class' => [
                Phake::mock(Metric::class),
                Phake::mock(Metric::class),
                Phake::mock(Metric::class),
                Phake::mock(Metric::class),
                Phake::mock(Metric::class),
            ],
            'Hard class' => [
                Phake::mock(Metric::class),
                Phake::mock(Metric::class),
                Phake::mock(Metric::class),
                Phake::mock(Metric::class),
                Phake::mock(Metric::class),
                Phake::mock(Metric::class),
            ],
            'also ignored' => 5678,
            'Bug class' => [
                Phake::mock(Metric::class),
                Phake::mock(Metric::class),
            ],
            'Small class' => [
                Phake::mock(Metric::class),
            ],
            'No metrics' => [],
        ];
        Phake::when($searchMetric)->__call('all', [])->thenReturn($allMetricsInViolations);
        Phake::when($allMetricsInViolations['Long class'][0])->__call('getName', [])->thenReturn('A');
        Phake::when($allMetricsInViolations['Long class'][1])->__call('getName', [])->thenReturn('B');
        Phake::when($allMetricsInViolations['Long class'][2])->__call('getName', [])->thenReturn('C');
        Phake::when($allMetricsInViolations['Long class'][3])->__call('getName', [])->thenReturn('D');
        Phake::when($allMetricsInViolations['Long class'][4])->__call('getName', [])->thenReturn('E');
        Phake::when($allMetricsInViolations['Hard class'][0])->__call('getName', [])->thenReturn('F');
        Phake::when($allMetricsInViolations['Hard class'][1])->__call('getName', [])->thenReturn('G');
        Phake::when($allMetricsInViolations['Hard class'][2])->__call('getName', [])->thenReturn('H');
        Phake::when($allMetricsInViolations['Hard class'][3])->__call('getName', [])->thenReturn('I');
        Phake::when($allMetricsInViolations['Hard class'][4])->__call('getName', [])->thenReturn('J');
        Phake::when($allMetricsInViolations['Hard class'][5])->__call('getName', [])->thenReturn('K');
        Phake::when($allMetricsInViolations['Bug class'][0])->__call('getName', [])->thenReturn('L');
        Phake::when($allMetricsInViolations['Bug class'][1])->__call('getName', [])->thenReturn('M');
        Phake::when($allMetricsInViolations['Small class'][0])->__call('getName', [])->thenReturn('N');

        $configSearches = [
            'Long class' => Phake::mock(SearchInterface::class),
            'Hard class' => Phake::mock(SearchInterface::class),
            'Bug class' => Phake::mock(SearchInterface::class),
            'Small class' => Phake::mock(SearchInterface::class),
            'No metrics' => Phake::mock(SearchInterface::class),
        ];
        Phake::when($configSearches['Long class'])->__call('getConfig', [])->thenReturn(['failIfFound' => false]);
        Phake::when($configSearches['Hard class'])->__call('getConfig', [])->thenReturn(['failIfFound' => true]);
        Phake::when($configSearches['Bug class'])->__call('getConfig', [])->thenReturn(['failIfFound' => false]);
        Phake::when($configSearches['Small class'])->__call('getConfig', [])->thenReturn(['failIfFound' => true]);

        Phake::when($metrics)->__call('get', ['searches'])->thenReturn($searchMetric);
        Phake::when($config)->__call('get', ['searches'])->thenReturn($configSearches);
        $actualOutput = '';
        Phake::when($output)->__call('writeln', [Phake::anyParameters()])->thenReturnCallback(
            static function (string $text) use (&$actualOutput): void {
                $actualOutput .= $text . PHP_EOL;
            }
        );

        $searchReporter = new SearchReporter($config, $output);
        $searchReporter->generate($metrics);

        Phake::verify($metrics)->__call('get', ['searches']);
        Phake::verify($config, Phake::times(5))->__call('get', ['searches']);
        Phake::verify($configSearches['Long class'])->__call('getConfig', []);
        Phake::verify($configSearches['Hard class'])->__call('getConfig', []);
        Phake::verify($configSearches['Bug class'])->__call('getConfig', []);
        Phake::verify($configSearches['Small class'])->__call('getConfig', []);
        Phake::verify($allMetricsInViolations['Long class'][0])->__call('getName', []);
        Phake::verify($allMetricsInViolations['Long class'][1])->__call('getName', []);
        Phake::verify($allMetricsInViolations['Long class'][2])->__call('getName', []);
        Phake::verify($allMetricsInViolations['Long class'][3])->__call('getName', []);
        Phake::verify($allMetricsInViolations['Long class'][4])->__call('getName', []);
        Phake::verify($allMetricsInViolations['Hard class'][0])->__call('getName', []);
        Phake::verify($allMetricsInViolations['Hard class'][1])->__call('getName', []);
        Phake::verify($allMetricsInViolations['Hard class'][2])->__call('getName', []);
        Phake::verify($allMetricsInViolations['Hard class'][3])->__call('getName', []);
        Phake::verify($allMetricsInViolations['Hard class'][4])->__call('getName', []);
        Phake::verify($allMetricsInViolations['Bug class'][0])->__call('getName', []);
        Phake::verify($allMetricsInViolations['Bug class'][1])->__call('getName', []);
        Phake::verify($allMetricsInViolations['Small class'][0])->__call('getName', []);

        // Verify the output mock to ensure the report is correctly done.
        $expectedFullOutput = <<<'OUTPUT'
        <info>Found 5 occurrences for search "Long class"</info>
        - A
        - B
        - C
        - D
        - E
        
        
        <error>Found 6 occurrences for search "Hard class"</error>
        - F
        - G
        - H
        - I
        - J
        … and 1 more
        
        
        <info>Found 2 occurrences for search "Bug class"</info>
        - L
        - M
        
        
        <error>Found 1 occurrences for search "Small class"</error>
        - N
        
        
        <info>Found 0 occurrences for search "No metrics"</info>
        
        

        OUTPUT;
        self::assertSame($expectedFullOutput, $actualOutput);
    }
}
