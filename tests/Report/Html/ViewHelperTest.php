<?php
declare(strict_types=1);

namespace Tests\Hal\Report\Html;

use Generator;
use Hal\Report\Html\ViewHelper;
use PHPUnit\Framework\TestCase;
use function array_map;
use function range;
use function sprintf;

final class ViewHelperTest extends TestCase
{
    /**
     * @return Generator<string, array{array<int>, float, float}
     */
    public function provideListsAndValuesToGetPercentilesFor(): Generator
    {
        // With consecutive values
        $list = range(0, 10);
        foreach ($list as $value) {
            yield 'Consecutive values from 0 to 10, value is ' . $value => [$list, $value, ($value / 10)];
        }

        // With random values
        $list = [1, 2, 3, 5, 7, 11, 13, 17, 19, 23, 29, 31, 37, 41, 43, 47, 49, 53, 59, 61, 67];
        yield 'Random values, value is at the beginning of the list' => [$list, 1, 0];
        yield 'Random values, value is at the end of the list' => [$list, 67, 1];
        yield 'Random values, value is in the list' => [$list, 47, 0.7];
        yield 'Random values, value is not in the list, but in bounds' => [$list, 35, 0.52];
        yield 'Random values, value is not in the list, under lower bound' => [$list, 0, 0];
        yield 'Random values, value is not in the list, over higher bound' => [$list, 100, 1];
    }

    /**
     * @dataProvider provideListsAndValuesToGetPercentilesFor
     * @param array<int> $list
     * @param float $value
     * @param float $expectedPercentage
     * @return void
     */
    //#[DataProvider('provideListsAndValuesToGetPercentilesFor')] TODO: PHPUnit 10.
    public function testICanFindGradientStyle(array $list, float $value, float $expectedPercentage): void
    {
        $viewHelper = new ViewHelper();
        $array = array_map(static fn (mixed $value): array => ['test' => $value], $list);
        $styleGradient = $viewHelper->gradientStyleFor($array, 'test', $value);

        $expectedStyle = sprintf(' style="background-color: hsla(203, 82%%, 76%%, %s);"', $expectedPercentage);
        self::assertSame($expectedStyle, $styleGradient);
    }
}
