<?php
namespace Test\Hal\Metric\Class_\Coupling;

use Hal\Component\Ast\ParserFactoryBridge;
use Hal\Component\Ast\ParserTraverserVisitorsAssigner;
use Hal\Metric\Class_\ClassEnumVisitor;
use Hal\Metric\Class_\Structural\SystemComplexityVisitor;
use Hal\Metric\Metrics;
use PhpParser\NodeVisitor\NameResolver;
use PhpParser\ParserFactory;
use PHPUnit\Framework\Attributes\DataProvider;

/**
 * @group metric
 * @group complexity
 * @group defect
 */
class SystemComplexityVisitorTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @dataProvider provideExamples
     */
    #[DataProvider('provideExamples')]
    public function testLackOfCohesionOfMethodsIsWellCalculated($filename, $class, $rdc, $rsc, $rsysc): void
    {
        $metrics = new Metrics();

        $parser = (new ParserFactoryBridge())->create();
        $traverser = new \PhpParser\NodeTraverser();
        (new ParserTraverserVisitorsAssigner())->assign($traverser, [
            new NameResolver(),
            new ClassEnumVisitor($metrics),
            new SystemComplexityVisitor($metrics),
        ]);

        $code = file_get_contents($filename);
        $stmts = $parser->parse($code);
        $traverser->traverse($stmts);

        $this->assertSame($rdc, $metrics->get('A')->get('relativeDataComplexity'));
        $this->assertSame($rsc, $metrics->get('A')->get('relativeStructuralComplexity'));
        $this->assertSame($rsysc, $metrics->get('A')->get('relativeSystemComplexity'));
    }

    public static function provideExamples()
    {
        return [
            [ __DIR__ . '/../../examples/systemcomplexity1.php', 'A', 0.5, 36.0, 36.5],
        ];
    }
}
