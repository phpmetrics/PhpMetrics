<?php

namespace Test\Hal\Component\Ast;

use Hal\Metric\ClassMetric;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\TestCase;
use Test\Hal\PhpUnit\WithAnalyzer;

#[Group('ast')]
class SupportedVersionTest extends TestCase
{
    use WithAnalyzer;

    /**
     * @dataProvider providesCases
     */
    #[DataProvider('providesCases')]
    public function testClassForPhpVersionIsCorrectlyParsed($filename, $classname, array $expected): void
    {
        $metrics = $this->analyze([__DIR__ . '/dataset/' . $filename . '.php']);

        /** @var ClassMetric $class */
        $class = $metrics->get($classname);
        $this->assertInstanceOf(ClassMetric::class, $class);
        foreach ($expected as $key => $value) {
            $this->assertEquals($value, $class->get($key), "[PHP $filename] Class $classname metric $key does not match expected value");
        }
    }

    public static function providesCases()
    {
        // for php < 7.0, the class is not supported
        if (version_compare(PHP_VERSION, '7.0.0', '<')) {
            return [];
        }

        $cases = [];
        if (version_compare(PHP_VERSION, '7.0.0', '>=')) {
            $cases['php7.1.void'] = ['php7.1.void', 'Foo', ['nbMethods' => 1, 'number_operands_unique' => 1]];
        }
        if (version_compare(PHP_VERSION, '7.2.0', '>=')) {
            $cases['php7.2.trailcomma'] = ['php7.2.trailcomma', 'Foo', ['nbMethods' => 1, 'number_operands_unique' => 2]];
        }
        if (version_compare(PHP_VERSION, '7.3.0', '>=')) {
            $cases['php7.3.heredoc'] = ['php7.3.heredoc', 'Foo', ['nbMethods' => 1, 'number_operands_unique' => 2]];
        }
        if (version_compare(PHP_VERSION, '7.4.0', '>=')) {
            $cases['php7.4.typedprop'] = ['php7.4.typedprop', 'Foo', ['nbMethods' => 1, 'number_operands_unique' => 1]];
        }
        if (version_compare(PHP_VERSION, '8.0.0', '>=')) {
            $cases['php8.0.uniontype'] = ['php8.0.uniontype', 'Foo', ['nbMethods' => 1, 'number_operands_unique' => 1]];
        }
        if (version_compare(PHP_VERSION, '8.1.0', '>=')) {
            $cases['php8.1.readonlyproperty'] = ['php8.1.readonlyproperty', 'Foo', ['nbMethods' => 0, 'number_operands_unique' => 2]];
        }
        if (version_compare(PHP_VERSION, '8.2.0', '>=')) {
            $cases['php8.2.truetype'] = ['php8.2.truetype', 'Foo', ['nbMethods' => 1, 'number_operands_unique' => 1]];
        }
        if (version_compare(PHP_VERSION, '8.3.0', '>=')) {
            $cases['php8.3.jsonconst'] = ['php8.3.jsonconst', 'Foo', ['nbMethods' => 1, 'number_operands_unique' => 1]];
        }
        if (version_compare(PHP_VERSION, '8.4.0', '>=')) {
            $cases['php8.4.propertyhook'] = ['php8.4.propertyhook', 'Foo', ['nbMethods' => 0, 'number_operands_unique' => 2]];
        }

        return $cases;
    }
}
