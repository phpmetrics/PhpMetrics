<?php
namespace Test\Hal\Metric\Class_\Structural;

use Hal\Component\Ast\ParserFactoryBridge;
use Hal\Component\Ast\ParserTraverserVisitorsAssigner;
use Hal\Metric\Class_\ClassEnumVisitor;
use Hal\Metric\Class_\Coupling\ExternalsVisitor;
use Hal\Metric\Class_\Structural\LcomVisitor;
use Hal\Metric\Metrics;
use PhpParser\ParserFactory;
use PHPUnit\Framework\Attributes\DataProvider;

class LcomVisitorTest extends \PHPUnit\Framework\TestCase
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
            new \PhpParser\NodeVisitor\NameResolver(),
            new ClassEnumVisitor($metrics),
            new LcomVisitor($metrics),
        ]);

        $code = file_get_contents($example);
        $stmts = $parser->parse($code);
        $traverser->traverse($stmts);

        $this->assertEquals($expected, $metrics->get($classname)->get('lcom'));
    }

    public static function provideExamples()
    {
        return [
            [ __DIR__ . '/../../examples/lcom1.php', 'MyClassA', 2]
        ];
    }
}
