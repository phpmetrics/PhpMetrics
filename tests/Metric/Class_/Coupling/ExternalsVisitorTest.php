<?php
namespace Test\Hal\Metric\Class_\Coupling;

use Hal\Metric\Class_\ClassEnumVisitor;
use Hal\Metric\Class_\Coupling\ExternalsVisitor;
use Hal\Metric\Metrics;
use PhpParser\ParserFactory;

/**
 * @group metric
 * @group externals
 * @group coupling
 */
class ExternalsVisitorTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @dataProvider provideExamples
     *
     * @param string $example
     * @param string $classname
     * @param string[] $expected
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
        $this->assertNotFalse($code);

        $stmts = $parser->parse($code);
        $this->assertNotNull($stmts);

        $traverser->traverse($stmts);

        $metric = $metrics->get($classname);
        $this->assertNotNull($metric);

        $this->assertSame($expected, $metric->get('externals'));
    }

    /** @return mixed[] */
    public function provideExamples()
    {
        return [
            [ __DIR__ . '/../../examples/externals1.php', 'A', ['H', 'C', 'B', 'D']],
            [ __DIR__ . '/../../examples/externals1.php', 'B', []],
            [ __DIR__ . '/../../examples/externals1.php', 'C', []],
            [ __DIR__ . '/../../examples/externals1.php', 'D', []],
            [ __DIR__ . '/../../examples/externals1.php', 'E', ['D', 'F', 'G']],
            [ __DIR__ . '/../../examples/externals1.php', 'F', ['G', 'H']],
            [ __DIR__ . '/../../examples/externals1.php', 'G', []],
            [ __DIR__ . '/../../examples/externals1.php', 'H', []],
            [ __DIR__ . '/../../examples/externals1.php', 'NS1\\A', ['NS2\\B']],
        ];
    }


    /**
     * @dataProvider provideExamplesAnnotation
     *
     * @param string $example
     * @param string $classname
     * @param string[] $expected
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
        $this->assertNotFalse($code);

        $stmts = $parser->parse($code);
        $this->assertNotNull($stmts);

        $traverser->traverse($stmts);

        $metric = $metrics->get($classname);
        $this->assertNotNull($metric);

        $this->assertSame($expected, $metric->get('externals'));
    }

    /** @return mixed[] */
    public function provideExamplesAnnotation()
    {
        return [
            [ __DIR__ . '/../../examples/annotations1.php', 'C\\A', ['A\\Route', 'B\\Json']],
        ];
    }
}
