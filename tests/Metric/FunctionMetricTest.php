<?php
declare(strict_types=1);

namespace Tests\Hal\Metric;

use Hal\Metric\FunctionMetric;
use PHPUnit\Framework\TestCase;

final class FunctionMetricTest extends TestCase
{
    public function testBagInteractionsInFunctionMetricContext(): void
    {
        $bagClass = new FunctionMetric('UnitTest');

        self::assertSame('UnitTest', $bagClass->getName());
        self::assertSame('UnitTest', $bagClass->get('name'));
        self::assertTrue($bagClass->has('name'));

        self::assertNull($bagClass->get('NOT A KEY'));

        $bagClass->set('other', 'FOO');
        self::assertSame('FOO', $bagClass->get('other'));

        $all = ['name' => 'UnitTest', 'other' => 'FOO'];
        self::assertSame($all, $bagClass->all());
        self::assertSame([...$all, '_type' => $bagClass::class], $bagClass->jsonSerialize());
    }
}
