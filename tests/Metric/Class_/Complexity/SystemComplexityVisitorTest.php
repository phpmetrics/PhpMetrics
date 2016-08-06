<?php
namespace Test\Hal\Metric\Class_\Coupling;

use Hal\Metric\Class_\ClassEnumVisitor;
use Hal\Metric\Class_\Complexity\McCabeVisitor;
use Hal\Metric\Class_\Structural\SystemComplexityVisitor;
use Hal\Metric\Metrics;
use PhpParser\ParserFactory;

/**
 * @group metric
 * @group complexity
 * @group defect
 */
class SystemComplexityVisitorTest extends \PHPUnit_Framework_TestCase
{


    /**
     * @dataProvider provideExamples
     */
    public function testLackOfCohesionOfMethodsIsWellCalculated($filename, $class, $rdc, $rsc, $rsysc)
    {
        $metrics = new Metrics();

        $parser = (new ParserFactory)->create(ParserFactory::PREFER_PHP7);
        $traverser = new \PhpParser\NodeTraverser();
        $traverser->addVisitor(new \PhpParser\NodeVisitor\NameResolver());
        $traverser->addVisitor(new ClassEnumVisitor($metrics));
        $traverser->addVisitor(new SystemComplexityVisitor($metrics));

        $code = file_get_contents($filename);
        $stmts = $parser->parse($code);
        $traverser->traverse($stmts);

        $this->assertSame($rdc, $metrics->get('A')->get('relativeDataComplexity'));
        $this->assertSame($rsc, $metrics->get('A')->get('relativeStructuralComplexity'));
        $this->assertSame($rsysc, $metrics->get('A')->get('relativeSystemComplexity'));
    }

    public function provideExamples()
    {
        return [
            [ __DIR__.'/../../examples/systemcomplexity1.php', 'A', 2.5, 1.0, 3.5],
        ];
    }

}