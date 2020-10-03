<?php
namespace Test\Hal\Metric\Class_\Coupling;

use Hal\Metric\Class_\ClassEnumVisitor;
use Hal\Metric\Class_\Complexity\KanDefectVisitor;
use Hal\Metric\Metrics;
use PhpParser\ParserFactory;

/**
 * @group metric
 * @group kan
 * @group defect
 */
class KanDefectVisitorTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @dataProvider provideExamples
     *
     * @param string $example
     * @param string $classname
     * @param float $expected
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
        $this->assertNotFalse($code);

        $stmts = $parser->parse($code);
        $this->assertNotNull($stmts);

        $traverser->traverse($stmts);

        $metric =  $metrics->get($classname);
        $this->assertNotNull($metric);

        $this->assertSame($expected, $metric->get('kanDefect'));
    }

    /** @return mixed[]*/
    public function provideExamples()
    {
        return [
            [ __DIR__ . '/../../examples/kan1.php', 'A', .89],
        ];
    }
}
