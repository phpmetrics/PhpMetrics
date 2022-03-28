<?php
declare(strict_types=1);

namespace Tests\Hal\Metric\Group;

use Generator;
use Hal\Metric\Group\Group;
use Hal\Metric\Metric;
use Hal\Metric\Metrics;
use Phake;
use PHPUnit\Framework\TestCase;
use function array_keys;

final class GroupTest extends TestCase
{
    /**
     * @return Generator<string, array{0: string, 1: array<string>}>
     */
    public function provideRegexToGroupMetrics(): Generator
    {
        yield 'Group by name containing "o"' => ['#o#', ['choc', 'knock']];
        yield 'Group by name ending by "ck"' => ['#ck$#', ['click', 'clack', 'knack', 'knock']];
        yield 'Group by name starting by "k"' => ['#^k#', ['knack', 'knock']];
        yield 'Group by name containing "€"' => ['#€#', []];
        yield 'Group with all elements' => ['#.*#', ['chic', 'choc', 'click', 'clack', 'knack', 'knock']];
    }

    /**
     * @dataProvider provideRegexToGroupMetrics
     * @param string $regex
     * @param array<string> $expected
     * @return void
     */
    //#[DataProvider('provideRegexToGroupMetrics')] TODO: PHPUnit 10.
    public function testICanGroupMetricsByRegex(string $regex, array $expected): void
    {
        $metricsMock = Phake::mock(Metrics::class);
        $metricMocks = [
            Phake::mock(Metric::class),
            Phake::mock(Metric::class),
            Phake::mock(Metric::class),
            Phake::mock(Metric::class),
            Phake::mock(Metric::class),
            Phake::mock(Metric::class),
        ];
        Phake::when($metricMocks[0])->__call('getName', [])->thenReturn('chic');
        Phake::when($metricMocks[1])->__call('getName', [])->thenReturn('choc');
        Phake::when($metricMocks[2])->__call('getName', [])->thenReturn('click');
        Phake::when($metricMocks[3])->__call('getName', [])->thenReturn('clack');
        Phake::when($metricMocks[4])->__call('getName', [])->thenReturn('knack');
        Phake::when($metricMocks[5])->__call('getName', [])->thenReturn('knock');
        Phake::when($metricsMock)->__call('all', [])->thenReturn($metricMocks);

        $group = new Group('uselessName', $regex);
        $newGroup = $group->reduceMetrics($metricsMock);

        self::assertSame($expected, array_keys($newGroup->all()));
        Phake::verify($metricsMock)->__call('all', []);
        Phake::verifyNoOtherInteractions($metricsMock);
    }
}
