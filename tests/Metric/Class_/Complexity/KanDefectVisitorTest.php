<?php
namespace Test\Hal\Metric\Class_\Coupling;

use Hal\Metric\Class_\ClassEnumVisitor;
use Hal\Metric\Class_\Complexity\CyclomaticComplexityVisitor;
use Hal\Metric\Class_\Complexity\KanDefectVisitor;
use Hal\Metric\Class_\Complexity\McCabeVisitor;
use Hal\Metric\Metrics;
use PhpParser\ParserFactory;

/**
 * @group metric
 * @group kan
 * @group defect
 */
class KanDefectVisitorTest extends \PHPUnit_Framework_TestCase {


    /**
     * @dataProvider provideExamples
     */
    public function testLackOfCohesionOfMethodsIsWellCalculated($example, $classname, $expected)
    {
        $metrics = new Metrics();

        $parser = (new ParserFactory)->create(ParserFactory::PREFER_PHP7);
        $traverser = new \PhpParser\NodeTraverser();
        $traverser->addVisitor(new \PhpParser\NodeVisitor\NameResolver());
        $traverser->addVisitor(new ClassEnumVisitor($metrics));
        $traverser->addVisitor(new KanDefectVisitor($metrics));

        $code = file_get_contents($example);
        $stmts = $parser->parse($code);
        $traverser->traverse($stmts);

        $this->assertSame($expected, $metrics->get($classname)->get('kanDefect'));
    }

    public function provideExamples()
    {
        return [
            [ __DIR__.'/../../examples/kan1.php', 'A', .89],
        ];
    }

}