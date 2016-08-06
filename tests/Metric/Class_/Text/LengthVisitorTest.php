<?php
namespace Test\Hal\Metric\Class_\Structural;

use Hal\Metric\Class_\ClassEnumVisitor;
use Hal\Metric\Class_\Text\HalsteadVisitor;
use Hal\Metric\Class_\Text\LengthVisitor;
use Hal\Metric\Metrics;
use PhpParser\Lexer;
use PhpParser\ParserFactory;

/**
 * @group loc
 * @group metric
 */
class LengthVisitorTest extends \PHPUnit_Framework_TestCase {


    /**
     * @dataProvider provideExamples
     */
    public function testLackOfCohesionOfMethodsIsWellCalculated($example, $functionName, $loc, $lloc, $cloc)
    {
        $metrics = new Metrics();

        $parser = (new ParserFactory)->create(ParserFactory::PREFER_PHP7);
        $traverser = new \PhpParser\NodeTraverser();
        $traverser->addVisitor(new \PhpParser\NodeVisitor\NameResolver());
        $traverser->addVisitor(new ClassEnumVisitor($metrics));
        $traverser->addVisitor(new LengthVisitor($metrics));

        $code = file_get_contents($example);
        $stmts = $parser->parse($code);
        $traverser->traverse($stmts);


        $this->assertEquals($lloc, $metrics->get($functionName)->get('lloc'));
        $this->assertEquals($cloc, $metrics->get($functionName)->get('cloc'));
        $this->assertEquals($loc, $metrics->get($functionName)->get('loc'));
    }

    public function provideExamples()
    {
        return [
            [ __DIR__.'/../../examples/loc1.php', 'A', 18, 11, 7],
        ];
    }

}