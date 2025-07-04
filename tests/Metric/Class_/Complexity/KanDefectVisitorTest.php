<?php
namespace Test\Hal\Metric\Class_\Coupling;

use Hal\Component\Ast\ParserFactoryBridge;
use Hal\Component\Ast\ParserTraverserVisitorsAssigner;
use Hal\Metric\Class_\ClassEnumVisitor;
use Hal\Metric\Class_\Complexity\KanDefectVisitor;
use Hal\Metric\Metrics;
use PhpParser\NodeVisitor\NameResolver;
use PhpParser\ParserFactory;
use PHPUnit\Framework\Attributes\DataProvider;

/**
 * @group metric
 * @group kan
 * @group defect
 */
class KanDefectVisitorTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @dataProvider provideExamples
     */
    #[DataProvider('provideExamples')]
    public function testLackOfCohesionOfMethodsIsWellCalculated($example, $classname, $expected): void
    {
        $metrics = new Metrics();

        $parser = (new ParserFactoryBridge())->create();
        $traverser = new \PhpParser\NodeTraverser();
        (new ParserTraverserVisitorsAssigner())->assign($traverser, [
            new NameResolver(),
            new ClassEnumVisitor($metrics),
            new KanDefectVisitor($metrics)
        ]);

        $code = file_get_contents($example);
        $stmts = $parser->parse($code);
        $traverser->traverse($stmts);

        $this->assertSame($expected, $metrics->get($classname)->get('kanDefect'));
    }

    public static function provideExamples()
    {
        return [
            [ __DIR__ . '/../../examples/kan1.php', 'A', .89],
        ];
    }
}
