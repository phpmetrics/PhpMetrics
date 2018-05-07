<?php
namespace Test\Hal\Metric\Class_\Structural;

use Hal\Metric\Class_\ClassEnumVisitor;
use Hal\Metric\Class_\Text\HalsteadVisitor;
use Hal\Metric\Metrics;
use PhpParser\ParserFactory;

/**
 * @group halstead
 * @group metric
 */
class HalsteadVisitorTest extends \PHPUnit_Framework_TestCase {


    /**
     * @dataProvider provideExamples
     */
    public function testLackOfCohesionOfMethodsIsWellCalculated($example, $functionName, $nbOperators, $nbOperands, $difficulty)
    {
        $metrics = new Metrics();

        $parser = (new ParserFactory)->create(ParserFactory::PREFER_PHP7);
        $traverser = new \PhpParser\NodeTraverser();
        $traverser->addVisitor(new \PhpParser\NodeVisitor\NameResolver());
        $traverser->addVisitor(new ClassEnumVisitor($metrics));
        $traverser->addVisitor(new HalsteadVisitor($metrics));

        $code = file_get_contents($example);
        $stmts = $parser->parse($code);
        $traverser->traverse($stmts);


        $this->assertEquals(
            $nbOperands,
            $metrics->get($functionName)->get('number_operands'),
            "Expected $nbOperands operands but got {$metrics->get($functionName)->get('number_operands')}"
        );
        $this->assertEquals(
            $nbOperators,
            $metrics->get($functionName)->get('number_operators'),
            "Expected $nbOperators operators but got {$metrics->get($functionName)->get('number_operators')}"
        );
        $this->assertEquals(
            $difficulty,
            $metrics->get($functionName)->get('difficulty'),
            "Expected difficulty $difficulty but got {$metrics->get($functionName)->get('difficulty')}"
        );
    }

    public function provideExamples()
    {
        return [
            [ __DIR__.'/../../examples/halstead1.php', 'twice', 2, 3, 1.5],
            [ __DIR__.'/../../examples/halstead2.php', 'maxi', 4, 6, 4.5],
            [ __DIR__.'/../../examples/halstead3.php', 'f_switch', 3, 9, 1.29],
            [ __DIR__.'/../../examples/halstead4.php', 'f_while', 9, 13, 10.4],
        ];
    }

}