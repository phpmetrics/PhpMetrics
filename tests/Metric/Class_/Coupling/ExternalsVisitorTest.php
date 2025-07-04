<?php
namespace Test\Hal\Metric\Class_\Coupling;

use Hal\Component\Ast\ParserFactoryBridge;
use Hal\Component\Ast\ParserTraverserVisitorsAssigner;
use Hal\Metric\Class_\ClassEnumVisitor;
use Hal\Metric\Class_\Coupling\ExternalsVisitor;
use Hal\Metric\Metrics;
use PhpParser\ParserFactory;
use PHPUnit\Framework\Attributes\DataProvider;

/**
 * @group metric
 * @group externals
 * @group coupling
 */
class ExternalsVisitorTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @dataProvider provideExamples
     */
    #[DataProvider('provideExamples')]
    public function testDependenciesAreFound($example, $classname, $expected): void
    {
        $metrics = new Metrics();

        $parser = (new ParserFactoryBridge())->create();
        $traverser = new \PhpParser\NodeTraverser();
        (new ParserTraverserVisitorsAssigner())->assign($traverser, [
            new \PhpParser\NodeVisitor\NameResolver(),
            new ClassEnumVisitor($metrics),
            new ExternalsVisitor($metrics)
        ]);

        $code = file_get_contents($example);
        $stmts = $parser->parse($code);
        $traverser->traverse($stmts);

        $this->assertSame($expected, $metrics->get($classname)->get('externals'));
    }

    public static function provideExamples()
    {
        return [
            [ __DIR__ . '/../../examples/externals1.php', 'A', ['H', 'C', 'B', 'D']],
            [ __DIR__ . '/../../examples/externals1.php', 'B', []],
            [ __DIR__ . '/../../examples/externals1.php', 'C', []],
            [ __DIR__ . '/../../examples/externals1.php', 'D', []],
            [ __DIR__ . '/../../examples/externals1.php', 'E', ['D', 'F', 'G']],
            [ __DIR__ . '/../../examples/externals1.php', 'F', ['G', 'H']],
            [ __DIR__ . '/../../examples/externals1.php', 'G', []],
            [ __DIR__ . '/../../examples/externals1.php', 'H', []],
            [ __DIR__ . '/../../examples/externals1.php', 'NS1\\A', ['NS2\\B']],
        ];
    }


    /**
     * @dataProvider provideExamplesAnnotation
     */
    #[DataProvider('provideExamplesAnnotation')]
    public function testDependenciesAreFoundEvenInAnnotation($example, $classname, $expected): void
    {
        $metrics = new Metrics();

        $parser = (new ParserFactoryBridge())->create();
        $traverser = new \PhpParser\NodeTraverser();
        (new ParserTraverserVisitorsAssigner())->assign($traverser, [
            new \PhpParser\NodeVisitor\NameResolver(),
            new ClassEnumVisitor($metrics),
            new ExternalsVisitor($metrics)
        ]);

        $code = file_get_contents($example);
        $stmts = $parser->parse($code);
        $traverser->traverse($stmts);

        $this->assertSame($expected, $metrics->get($classname)->get('externals'));
    }

    public static function provideExamplesAnnotation()
    {
        return [
            [ __DIR__ . '/../../examples/annotations1.php', 'C\\A', ['A\\Route', 'B\\Json']],
        ];
    }
}
