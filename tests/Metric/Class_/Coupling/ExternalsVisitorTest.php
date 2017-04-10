<?php
namespace Test\Hal\Metric\Class_\Coupling;

use Hal\Metric\Class_\ClassEnumVisitor;
use Hal\Metric\Class_\Complexity\McCabeVisitor;
use Hal\Metric\Class_\Coupling\ExternalsVisitor;
use Hal\Metric\Metrics;
use PhpParser\ParserFactory;

/**
 * @group metric
 * @group externals
 * @group coupling
 */
class ExternalsVisitorTest extends \PHPUnit_Framework_TestCase {


    /**
     * @dataProvider provideExamples
     */
    public function testDependenciesAreFound($example, $classname, $expected)
    {
        $metrics = new Metrics();

        $parser = (new ParserFactory)->create(ParserFactory::PREFER_PHP7);
        $traverser = new \PhpParser\NodeTraverser();
        $traverser->addVisitor(new \PhpParser\NodeVisitor\NameResolver());
        $traverser->addVisitor(new ClassEnumVisitor($metrics));
        $traverser->addVisitor(new ExternalsVisitor($metrics));

        $code = file_get_contents($example);
        $stmts = $parser->parse($code);
        $traverser->traverse($stmts);

        $this->assertSame($expected, $metrics->get($classname)->get('externals'));
    }

    public function provideExamples()
    {
        return [
            [ __DIR__.'/../../examples/externals1.php', 'A', ['H', 'C', 'B', 'D']],
            [ __DIR__.'/../../examples/externals1.php', 'B', []],
            [ __DIR__.'/../../examples/externals1.php', 'C', []],
            [ __DIR__.'/../../examples/externals1.php', 'D', []],
            [ __DIR__.'/../../examples/externals1.php', 'E', ['D', 'F', 'G']],
            [ __DIR__.'/../../examples/externals1.php', 'F', ['G', 'H']],
            [ __DIR__.'/../../examples/externals1.php', 'G', []],
            [ __DIR__.'/../../examples/externals1.php', 'H', []],
            [ __DIR__.'/../../examples/externals1.php', 'NS1\\A', ['NS2\\B']],
        ];
    }


    /**
     * @dataProvider provideExamplesAnnotation
     */
    public function testDependenciesAreFoundEvenInAnnotation($example, $classname, $expected)
    {
        $metrics = new Metrics();

        $parser = (new ParserFactory)->create(ParserFactory::PREFER_PHP7);
        $traverser = new \PhpParser\NodeTraverser();
        $traverser->addVisitor(new \PhpParser\NodeVisitor\NameResolver());
        $traverser->addVisitor(new ClassEnumVisitor($metrics));
        $traverser->addVisitor(new ExternalsVisitor($metrics));

        $code = file_get_contents($example);
        $stmts = $parser->parse($code);
        $traverser->traverse($stmts);

        $this->assertSame($expected, $metrics->get($classname)->get('externals'));
    }

    public function provideExamplesAnnotation()
    {
        return [
            [ __DIR__.'/../../examples/annotations1.php', 'C\\A', ['A\\Route', 'B\\Json']],
        ];
    }

}