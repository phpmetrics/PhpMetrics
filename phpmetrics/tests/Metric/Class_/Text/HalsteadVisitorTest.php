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
class HalsteadVisitorTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @dataProvider provideExamples
     *
     * @param string $example
     * @param string $name
     * @param int $operators
     * @param int $operands
     * @param float $difficulty
     */
    public function testLackOfCohesionOfMethodsIsWellCalculated($example, $name, $operators, $operands, $difficulty)
    {
        $metrics = new Metrics();

        $parser = (new ParserFactory)->create(ParserFactory::PREFER_PHP7);
        $traverser = new \PhpParser\NodeTraverser();
        $traverser->addVisitor(new \PhpParser\NodeVisitor\NameResolver());
        $traverser->addVisitor(new ClassEnumVisitor($metrics));
        $traverser->addVisitor(new HalsteadVisitor($metrics));

        $code = file_get_contents($example);
        $this->assertNotFalse($code);

        $stmts = $parser->parse($code);
        $this->assertNotNull($stmts);

        $traverser->traverse($stmts);

        $metric = $metrics->get($name);
        $this->assertNotNull($metric);

        $this->assertEquals(
            $operands,
            $metric->get('number_operands'),
            "Expected $operands operands but got {$metric->get('number_operands')}"
        );
        $this->assertEquals(
            $operators,
            $metric->get('number_operators'),
            "Expected $operators operators but got {$metric->get('number_operators')}"
        );
        $this->assertEquals(
            $difficulty,
            $metric->get('difficulty'),
            "Expected difficulty $difficulty but got {$metric->get('difficulty')}"
        );
    }

    /** @return mixed[] */
    public function provideExamples()
    {
        return [
            [ __DIR__ . '/../../examples/halstead1.php', 'twice', 2, 3, 1.5],
            [ __DIR__ . '/../../examples/halstead2.php', 'maxi', 4, 6, 4.5],
            [ __DIR__ . '/../../examples/halstead3.php', 'f_switch', 3, 9, 1.29],
            [ __DIR__ . '/../../examples/halstead4.php', 'f_while', 9, 13, 10.4],
        ];
    }
}
