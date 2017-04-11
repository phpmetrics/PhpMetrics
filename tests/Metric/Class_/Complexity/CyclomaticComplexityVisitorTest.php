<?php
namespace Test\Hal\Metric\Class_\Coupling;

use Hal\Metric\Class_\ClassEnumVisitor;
use Hal\Metric\Class_\Complexity\CyclomaticComplexityVisitor;
use Hal\Metric\Class_\Complexity\McCabeVisitor;
use Hal\Metric\Metrics;
use PhpParser\ParserFactory;

class CyclomaticComplexityVisitorTest extends \PHPUnit_Framework_TestCase {


    /**
     * @dataProvider provideExamplesForClasses
     */
    public function testCyclomaticComplexityOfClassesIsWellCalculated($example, $classname, $expectedCcn)
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

        $this->assertSame($expectedCcn, $metrics->get($classname)->get('ccn'));
    }

    /**
     * @dataProvider provideExamplesForMethods
     */
    public function testCyclomaticComplexityOfMethodsIsWellCalculated($example, $classname, $expectedCcnMethodMax)
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

        $this->assertSame($expectedCcnMethodMax, $metrics->get($classname)->get('ccnMethodMax'));
    }

    public function provideExamplesForClasses()
    {
        return [
            [ __DIR__.'/../../examples/cyclomatic1.php', 'A', 4],
            [ __DIR__.'/../../examples/cyclomatic1.php', 'B', 5],
        ];
    }

    public function provideExamplesForMethods()
    {
        return [
            [ __DIR__.'/../../examples/cyclomatic1.php', 'A', 3],
            [ __DIR__.'/../../examples/cyclomatic1.php', 'B', 5],
        ];
    }

}