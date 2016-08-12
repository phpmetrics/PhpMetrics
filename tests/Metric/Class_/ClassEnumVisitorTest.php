<?php
namespace Test\Hal\Metric\Class_\Coupling;

use Hal\Metric\Class_\ClassEnumVisitor;
use Hal\Metric\Class_\Complexity\CyclomaticComplexityVisitor;
use Hal\Metric\Class_\Complexity\McCabeVisitor;
use Hal\Metric\Metrics;
use PhpParser\ParserFactory;

class CyclomaticComplexityVisitorTest extends \PHPUnit_Framework_TestCase {


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
        $traverser->addVisitor(new CyclomaticComplexityVisitor($metrics));

        $code = file_get_contents($example);
        $stmts = $parser->parse($code);
        $traverser->traverse($stmts);

        $this->assertSame($expected, $metrics->get($classname)->get('ccn'));
    }

    public function provideExamples()
    {
        return [
            [ __DIR__.'/../../examples/cyclomatic1.php', 'A', 3],
            [ __DIR__.'/../../examples/cyclomatic1.php', 'B', 3],
        ];
    }

}