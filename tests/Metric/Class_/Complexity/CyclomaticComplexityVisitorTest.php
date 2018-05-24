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
            'A' => [ __DIR__.'/../../examples/cyclomatic1.php', 'A', 8],
            'B' => [ __DIR__.'/../../examples/cyclomatic1.php', 'B', 4],
            'Foo\\C' => [ __DIR__.'/../../examples/cyclomatic_anon.php', 'Foo\\C', 1],
            'SwitchCase' => [ __DIR__.'/../../examples/cyclomatic_full.php', 'SwitchCase', 4],
            'IfElseif' => [ __DIR__.'/../../examples/cyclomatic_full.php', 'IfElseif', 7],
            'Loops' => [ __DIR__.'/../../examples/cyclomatic_full.php', 'Loops', 5],
            'CatchIt' => [ __DIR__.'/../../examples/cyclomatic_full.php', 'CatchIt', 3],
            'Logical' => [ __DIR__.'/../../examples/cyclomatic_full.php', 'Logical', 11],
        ];
    }

    public function provideExamplesForMethods()
    {
        return [
            [ __DIR__.'/../../examples/cyclomatic1.php', 'A', 6],
            [ __DIR__.'/../../examples/cyclomatic1.php', 'B', 4],
        ];
    }

}