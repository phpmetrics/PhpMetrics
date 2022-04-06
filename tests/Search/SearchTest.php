<?php
declare(strict_types=1);

namespace Tests\Hal\Search;

use Generator;
use Hal\Exception\ConfigException\SearchValidationException;
use Hal\Metric\ClassMetric;
use Hal\Metric\InterfaceMetric;
use Hal\Metric\Metric;
use Hal\Metric\Registry;
use Hal\Search\Search;
use Phake;
use Phake\IMock;
use PHPUnit\Framework\TestCase;
use function in_array;

final class SearchTest extends TestCase
{
    public function testICanBuildSearchObjects(): void
    {
        $rawArrayOfSearches = [
            'empty' => [],
            'withData' => ['foo' => 'FOO'],
            'withNestedData' => ['foo' => 'FOO-FOO', 'bar' => ['bar' => ['bar' => 'BAR']]],
        ];

        [$searchA, $searchB, $searchC] = Search::buildListFromArray($rawArrayOfSearches);

        self::assertSame('empty', $searchA->getName());
        self::assertSame([], $searchA->getConfig());
        self::assertSame('withData', $searchB->getName());
        self::assertSame(['foo' => 'FOO'], $searchB->getConfig());
        self::assertSame('withNestedData', $searchC->getName());
        self::assertSame(['foo' => 'FOO-FOO', 'bar' => ['bar' => ['bar' => 'BAR']]], $searchC->getConfig());
    }

    /**
     * @return Generator<string, array{IMock&Metric, Search, bool, callable(IMock&Metric): void}>
     */
    public function provideSearchAndMetrics(): Generator
    {
        [$search] = Search::buildListFromArray(['test' => []]);
        $metric = Phake::mock(Metric::class);
        $checkCallback = static function (IMock&Metric $metric): void {
            Phake::verifyNoInteraction($metric);
        };
        yield 'No search configuration' => [$metric, $search, false, $checkCallback];

        [$search] = Search::buildListFromArray(['test' => ['type' => 'class']]);
        $metric = Phake::mock(Metric::class);
        $checkCallback = static function (IMock&Metric $metric): void {
            Phake::verifyNoInteraction($metric);
        };
        yield 'Search type "class", none found' => [$metric, $search, false, $checkCallback];

        [$search] = Search::buildListFromArray(['test' => ['type' => 'class']]);
        $metric = Phake::mock(ClassMetric::class);
        $checkCallback = static function (IMock&Metric $metric): void {
            Phake::verifyNoInteraction($metric);
        };
        yield 'Search type "class", found' => [$metric, $search, true, $checkCallback];

        [$search] = Search::buildListFromArray(['test' => ['type' => 'class', 'failIfFound' => true]]);
        $metric = Phake::mock(ClassMetric::class);
        Phake::when($metric)->__call('get', ['was-not-expected-by'])->thenReturn(null);
        $checkCallback = static function (IMock&Metric $metric): void {
            Phake::verify($metric)->__call('get', ['was-not-expected-by']);
            Phake::verify($metric)->__call('set', ['was-not-expected', true]);
            Phake::verify($metric)->__call('set', ['was-not-expected-by', ['test']]);
            Phake::verifyNoOtherInteractions($metric);
        };
        yield 'Search type "class", found. Fail if found.' => [$metric, $search, true, $checkCallback];

        [$search] = Search::buildListFromArray(['test' => ['type' => 'class']]);
        $metric = Phake::mock(InterfaceMetric::class);
        $checkCallback = static function (IMock&Metric $metric): void {
            Phake::verifyNoInteraction($metric);
        };
        yield 'Search type "class", interface found' => [$metric, $search, false, $checkCallback];

        [$search] = Search::buildListFromArray(['test' => ['type' => 'interface']]);
        $metric = Phake::mock(Metric::class);
        $checkCallback = static function (IMock&Metric $metric): void {
            Phake::verifyNoInteraction($metric);
        };
        yield 'Search type "interface", none found' => [$metric, $search, false, $checkCallback];

        [$search] = Search::buildListFromArray(['test' => ['type' => 'interface']]);
        $metric = Phake::mock(InterfaceMetric::class);
        $checkCallback = static function (IMock&Metric $metric): void {
            Phake::verifyNoInteraction($metric);
        };
        yield 'Search type "interface", found' => [$metric, $search, true, $checkCallback];

        [$search] = Search::buildListFromArray(['test' => ['type' => 'interface', 'failIfFound' => true]]);
        $metric = Phake::mock(InterfaceMetric::class);
        Phake::when($metric)->__call('get', ['was-not-expected-by'])->thenReturn(null);
        $checkCallback = static function (IMock&Metric $metric): void {
            Phake::verify($metric)->__call('get', ['was-not-expected-by']);
            Phake::verify($metric)->__call('set', ['was-not-expected', true]);
            Phake::verify($metric)->__call('set', ['was-not-expected-by', ['test']]);
            Phake::verifyNoOtherInteractions($metric);
        };
        yield 'Search type "interface", found. Fail if found.' => [$metric, $search, true, $checkCallback];

        [$search] = Search::buildListFromArray(['test' => ['nameMatches' => '^a|b|c$']]);
        $metric = Phake::mock(Metric::class);
        Phake::when($metric)->__call('getName', [])->thenReturn('d');
        $checkCallback = static function (IMock&Metric $metric): void {
            Phake::verify($metric)->__call('getName', []);
            Phake::verifyNoOtherInteractions($metric);
        };
        yield 'Search name matches, but does not match' => [$metric, $search, false, $checkCallback];

        [$search] = Search::buildListFromArray(['test' => ['nameMatches' => '^a|b|c$']]);
        $metric = Phake::mock(Metric::class);
        Phake::when($metric)->__call('getName', [])->thenReturn('B');
        $checkCallback = static function (IMock&Metric $metric): void {
            Phake::verify($metric)->__call('getName', []);
            Phake::verifyNoOtherInteractions($metric);
        };
        yield 'Search name matches, and matches' => [$metric, $search, true, $checkCallback];

        [$search] = Search::buildListFromArray(['test' => ['nameMatches' => '^a|b|c$', 'failIfFound' => true]]);
        $metric = Phake::mock(Metric::class);
        Phake::when($metric)->__call('getName', [])->thenReturn('B');
        Phake::when($metric)->__call('get', ['was-not-expected-by'])->thenReturn(null);
        $checkCallback = static function (IMock&Metric $metric): void {
            Phake::verify($metric)->__call('getName', []);
            Phake::verify($metric)->__call('get', ['was-not-expected-by']);
            Phake::verify($metric)->__call('set', ['was-not-expected', true]);
            Phake::verify($metric)->__call('set', ['was-not-expected-by', ['test']]);
            Phake::verifyNoOtherInteractions($metric);
        };
        yield 'Search name matches, and matches. Fail if found.' => [$metric, $search, true, $checkCallback];

        [$search] = Search::buildListFromArray(['test' => ['instanceOf' => ['\\A', 'B']]]);
        $metric = Phake::mock(Metric::class);
        Phake::when($metric)->__call('get', ['implements'])->thenReturn('');
        $checkCallback = static function (IMock&Metric $metric): void {
            Phake::verify($metric)->__call('get', ['implements']);
            Phake::verifyNoOtherInteractions($metric);
        };
        yield 'Search instance of, none found' => [$metric, $search, false, $checkCallback];

        [$search] = Search::buildListFromArray(['test' => ['instanceOf' => ['\\A', 'B']]]);
        $metric = Phake::mock(Metric::class);
        Phake::when($metric)->__call('get', ['implements'])->thenReturn(['A']);
        $checkCallback = static function (IMock&Metric $metric): void {
            Phake::verify($metric)->__call('get', ['implements']);
            Phake::verifyNoOtherInteractions($metric);
        };
        yield 'Search instance of, but only 1' => [$metric, $search, false, $checkCallback];

        [$search] = Search::buildListFromArray(['test' => ['instanceOf' => ['\\A', 'B']]]);
        $metric = Phake::mock(Metric::class);
        Phake::when($metric)->__call('get', ['implements'])->thenReturn(['A', 'B']);
        $checkCallback = static function (IMock&Metric $metric): void {
            Phake::verify($metric)->__call('get', ['implements']);
            Phake::verifyNoOtherInteractions($metric);
        };
        yield 'Search instance of, and got all' => [$metric, $search, true, $checkCallback];

        [$search] = Search::buildListFromArray(['test' => ['instanceOf' => ['\\A', 'B'], 'failIfFound' => true]]);
        $metric = Phake::mock(Metric::class);
        Phake::when($metric)->__call('get', ['implements'])->thenReturn(['A', 'B']);
        Phake::when($metric)->__call('get', ['was-not-expected-by'])->thenReturn(null);
        $checkCallback = static function (IMock&Metric $metric): void {
            Phake::verify($metric)->__call('get', ['implements']);
            Phake::verify($metric)->__call('get', ['was-not-expected-by']);
            Phake::verify($metric)->__call('set', ['was-not-expected', true]);
            Phake::verify($metric)->__call('set', ['was-not-expected-by', ['test']]);
            Phake::verifyNoOtherInteractions($metric);
        };
        yield 'Search instance of, and got all. Fail if found.' => [$metric, $search, true, $checkCallback];

        [$search] = Search::buildListFromArray(['test' => ['instanceOf' => ['\\A', 'B']]]);
        $metric = Phake::mock(Metric::class);
        Phake::when($metric)->__call('get', ['implements'])->thenReturn(['A', 'B', 'C']);
        $checkCallback = static function (IMock&Metric $metric): void {
            Phake::verify($metric)->__call('get', ['implements']);
            Phake::verifyNoOtherInteractions($metric);
        };
        yield 'Search instance of, and got more than all' => [$metric, $search, true, $checkCallback];

        [$search] = Search::buildListFromArray(['test' => ['instanceOf' => ['\\A', 'B'], 'failIfFound' => true]]);
        $metric = Phake::mock(Metric::class);
        Phake::when($metric)->__call('get', ['implements'])->thenReturn(['A', 'B', 'C']);
        Phake::when($metric)->__call('get', ['was-not-expected-by'])->thenReturn(null);
        $checkCallback = static function (IMock&Metric $metric): void {
            Phake::verify($metric)->__call('get', ['implements']);
            Phake::verify($metric)->__call('get', ['was-not-expected-by']);
            Phake::verify($metric)->__call('set', ['was-not-expected', true]);
            Phake::verify($metric)->__call('set', ['was-not-expected-by', ['test']]);
            Phake::verifyNoOtherInteractions($metric);
        };
        yield 'Search instance of, and got more than all. Fail if found.' => [$metric, $search, true, $checkCallback];

        [$search] = Search::buildListFromArray(['test' => ['usesClasses' => ['^(A|B)$', '^C', 'D$']]]);
        $metric = Phake::mock(Metric::class);
        Phake::when($metric)->__call('get', ['externals'])->thenReturn('');
        $checkCallback = static function (IMock&Metric $metric): void {
            Phake::verify($metric)->__call('get', ['externals']);
            Phake::verifyNoOtherInteractions($metric);
        };
        yield 'Search uses of, none found' => [$metric, $search, false, $checkCallback];

        [$search] = Search::buildListFromArray(['test' => ['usesClasses' => ['^(A|B)$', '^C', 'D$']]]);
        $metric = Phake::mock(Metric::class);
        Phake::when($metric)->__call('get', ['externals'])->thenReturn(['XxX', 'xXx', 'xxx', 'XXX']);
        $checkCallback = static function (IMock&Metric $metric): void {
            Phake::verify($metric)->__call('get', ['externals']);
            Phake::verifyNoOtherInteractions($metric);
        };
        yield 'Search uses of, none found but externals exist' => [$metric, $search, false, $checkCallback];

        [$search] = Search::buildListFromArray(['test' => ['usesClasses' => ['^(A|B)$', '^C', 'D$']]]);
        $metric = Phake::mock(Metric::class);
        Phake::when($metric)->__call('get', ['externals'])->thenReturn(['b']);
        $checkCallback = static function (IMock&Metric $metric): void {
            Phake::verify($metric)->__call('get', ['externals']);
            Phake::verifyNoOtherInteractions($metric);
        };
        yield 'Search uses of, found 1st time' => [$metric, $search, true, $checkCallback];

        [$search] = Search::buildListFromArray(['test' => ['usesClasses' => ['^(A|B)$', '^C', 'D$']]]);
        $metric = Phake::mock(Metric::class);
        Phake::when($metric)->__call('get', ['externals'])->thenReturn(['XxX', 'classThatWorks']);
        $checkCallback = static function (IMock&Metric $metric): void {
            Phake::verify($metric)->__call('get', ['externals']);
            Phake::verifyNoOtherInteractions($metric);
        };
        yield 'Search uses of, found 2nd time' => [$metric, $search, true, $checkCallback];

        [$search] = Search::buildListFromArray(['test' => ['usesClasses' => ['^(A|B)$', '^C', 'D$']]]);
        $metric = Phake::mock(Metric::class);
        Phake::when($metric)->__call('get', ['externals'])->thenReturn(['XxX', 'xXx', 'NowWeEnd']);
        $checkCallback = static function (IMock&Metric $metric): void {
            Phake::verify($metric)->__call('get', ['externals']);
            Phake::verifyNoOtherInteractions($metric);
        };
        yield 'Search uses of, found 3rd time' => [$metric, $search, true, $checkCallback];

        [$search] = Search::buildListFromArray(
            ['test' => ['usesClasses' => ['^(A|B)$', '^C', 'D$'], 'failIfFound' => true]]
        );
        $metric = Phake::mock(Metric::class);
        Phake::when($metric)->__call('get', ['externals'])->thenReturn(['XxX', 'xXx', 'Completed']);
        Phake::when($metric)->__call('get', ['was-not-expected-by'])->thenReturn(['elder-test', 'previous-test']);
        $checkCallback = static function (IMock&Metric $metric): void {
            Phake::verify($metric)->__call('get', ['externals']);
            Phake::verify($metric)->__call('get', ['was-not-expected-by']);
            Phake::verify($metric)->__call('set', ['was-not-expected', true]);
            Phake::verify($metric)->__call('set', ['was-not-expected-by', ['elder-test', 'previous-test', 'test']]);
            Phake::verifyNoOtherInteractions($metric);
        };
        yield 'Search uses of, found. Fail if found, with previous findings.'
            => [$metric, $search, true, $checkCallback];

        foreach (Registry::allForStructures() as $structName) {
            // Represent every managed operator that passes the regex check. Added ">=<" for default case.
            foreach (['', '=', '>', '<', '>=', '=>', '<=', '=<', '>=<'] as $operator) {
                [$search] = Search::buildListFromArray(['test' => [$structName => $operator . '0.5']]);
                $metric = Phake::mock(Metric::class);
                Phake::when($metric)->__call('get', [$structName])->thenReturn(0.5);
                $match = in_array($operator, ['', '=', '>=', '=>', '<=', '=<'], true);
                $checkCallback = static function (IMock&Metric $metric) use ($structName): void {
                    Phake::verify($metric)->__call('get', [$structName]);
                    Phake::verifyNoOtherInteractions($metric);
                };
                yield 'Search ' . $structName . ', operator "' . $operator . '", metric is same value' =>
                    [$metric, $search, $match, $checkCallback];

                [$search] = Search::buildListFromArray(['test' => [$structName => $operator . '0.5']]);
                $metric = Phake::mock(Metric::class);
                Phake::when($metric)->__call('get', [$structName])->thenReturn(0.3);
                $match = in_array($operator, ['<=', '<', '=<'], true);
                $checkCallback = static function (IMock&Metric $metric) use ($structName): void {
                    Phake::verify($metric)->__call('get', [$structName]);
                    Phake::verifyNoOtherInteractions($metric);
                };
                yield 'Search ' . $structName . ', operator "' . $operator . '", metric is less' =>
                    [$metric, $search, $match, $checkCallback];

                [$search] = Search::buildListFromArray(['test' => [$structName => $operator . '0.5']]);
                $metric = Phake::mock(Metric::class);
                Phake::when($metric)->__call('get', [$structName])->thenReturn(2.8);
                $match = in_array($operator, ['>=', '>', '=>'], true);
                $checkCallback = static function (IMock&Metric $metric) use ($structName): void {
                    Phake::verify($metric)->__call('get', [$structName]);
                    Phake::verifyNoOtherInteractions($metric);
                };
                yield 'Search ' . $structName . ', operator "' . $operator . '", metric is greater' =>
                    [$metric, $search, $match, $checkCallback];
            }
        }
    }

    /**
     * @dataProvider provideSearchAndMetrics
     * @param IMock&Metric $metric
     * @param Search $search
     * @param bool $matches
     * @param callable(IMock&Metric): void $checkMetricInteraction
     * @return void
     */
    //#[DataProvider('provideSearchAndMetrics')] TODO: PHPUnit 10
    public function testICanMatchSearchesWithMetrics(
        IMock&Metric $metric,
        Search $search,
        bool $matches,
        callable $checkMetricInteraction
    ): void {
        self::assertSame($matches, $search->matches($metric));
        $checkMetricInteraction($metric);
    }

    public function testExceptionIsThrownWhenCustomSearchMetricsIsInvalid(): void
    {
        [$search] = Search::buildListFromArray(['test' => ['loc' => '= invalidValue']]);
        $metric = Phake::mock(Metric::class);

        $this->expectExceptionObject(SearchValidationException::invalidCustomMetricComparison('= invalidValue'));

        $search->matches($metric);
    }
}
