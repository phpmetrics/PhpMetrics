<?php
namespace Test\Hal\Metric\Class_\Structural;

use Hal\Metric\Class_\ClassEnumVisitor;
use Hal\Metric\Class_\Structural\LcomVisitor;
use Hal\Metric\Metrics;
use PhpParser\ParserFactory;

class LcomVisitorTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @dataProvider provideExamples
     *
     * @param string $example
     * @param string $classname
     * @param int $expected
     */
    public function testLackOfCohesionOfMethodsIsWellCalculated($example, $classname, $expected)
    {
        $metrics = new Metrics();

        $parser = (new ParserFactory)->create(ParserFactory::PREFER_PHP7);
        $traverser = new \PhpParser\NodeTraverser();
        $traverser->addVisitor(new \PhpParser\NodeVisitor\NameResolver());
        $traverser->addVisitor(new ClassEnumVisitor($metrics));
        $traverser->addVisitor(new LcomVisitor($metrics));

        $code = file_get_contents($example);
        $this->assertNotFalse($code);

        $stmts = $parser->parse($code);
        $this->assertNotNull($stmts);

        $traverser->traverse($stmts);

        $metric = $metrics->get($classname);
        $this->assertNotNull($metric);

        $this->assertEquals($expected, $metric->get('lcom'));
    }

    /** @return mixed[] */
    public function provideExamples()
    {
        return [
            [ __DIR__ . '/../../examples/lcom1.php', 'MyClassA', 2]
        ];
    }
}
