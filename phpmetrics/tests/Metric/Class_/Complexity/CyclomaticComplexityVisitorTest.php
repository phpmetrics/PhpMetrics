<?php
namespace Test\Hal\Metric\Class_\Coupling;

use Hal\Metric\Class_\ClassEnumVisitor;
use Hal\Metric\Class_\Complexity\CyclomaticComplexityVisitor;
use Hal\Metric\Metrics;
use PhpParser\NodeTraverser;
use PhpParser\NodeVisitor\NameResolver;
use PhpParser\ParserFactory;

class CyclomaticComplexityVisitorTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @dataProvider provideExamplesForCcn
     *
     * @param string $example
     * @param string $classname
     * @param int $expectedCcn
     */
    public function testCcnOfClassesIsWellCalculated($example, $classname, $expectedCcn)
    {
        $metrics = new Metrics();

        $parser = (new ParserFactory)->create(ParserFactory::PREFER_PHP7);
        $traverser = new NodeTraverser();
        $traverser->addVisitor(new NameResolver());
        $traverser->addVisitor(new ClassEnumVisitor($metrics));
        $traverser->addVisitor(new CyclomaticComplexityVisitor($metrics));

        $code = file_get_contents($example);
        $this->assertNotFalse($code);

        $stmts = $parser->parse($code);
        $this->assertNotNull($stmts);

        $traverser->traverse($stmts);

        $metric = $metrics->get($classname);
        $this->assertNotNull($metric);

        $this->assertSame($expectedCcn, $metric->get('ccn'));
    }

    /**
     * @dataProvider provideExamplesForWmc
     *
     * @param string $example
     * @param string $classname
     * @param int $expectedWmc
     */
    public function testWeightedMethodCountOfClassesIsWellCalculated($example, $classname, $expectedWmc)
    {
        $metrics = new Metrics();

        $parser = (new ParserFactory)->create(ParserFactory::PREFER_PHP7);
        $traverser = new NodeTraverser();
        $traverser->addVisitor(new NameResolver());
        $traverser->addVisitor(new ClassEnumVisitor($metrics));
        $traverser->addVisitor(new CyclomaticComplexityVisitor($metrics));

        $code = file_get_contents($example);
        $this->assertNotFalse($code);

        $stmts = $parser->parse($code);
        $this->assertNotNull($stmts);

        $traverser->traverse($stmts);

        $metric = $metrics->get($classname);
        $this->assertNotNull($metric);
        $this->assertSame($expectedWmc, $metric->get('wmc'));
    }

    /**
     * @dataProvider provideExamplesForMaxCc
     *
     * @param string $example
     * @param string $classname
     * @param int $expected
     */
    public function testMaximalCyclomaticComplexityOfMethodsIsWellCalculated($example, $classname, $expected)
    {
        $metrics = new Metrics();

        $parser = (new ParserFactory)->create(ParserFactory::PREFER_PHP7);
        $traverser = new NodeTraverser();
        $traverser->addVisitor(new NameResolver());
        $traverser->addVisitor(new ClassEnumVisitor($metrics));
        $traverser->addVisitor(new CyclomaticComplexityVisitor($metrics));

        $code = file_get_contents($example);
        $this->assertNotFalse($code);

        $stmts = $parser->parse($code);
        $this->assertNotNull($stmts);

        $traverser->traverse($stmts);

        $metric = $metrics->get($classname);
        $this->assertNotNull($metric);

        $this->assertSame($expected, $metric->get('ccnMethodMax'));
    }

    /** @return array<string,mixed> */
    public static function provideExamplesForWmc()
    {
        return [
            'A' => [__DIR__ . '/../../examples/cyclomatic1.php', 'A', 10],
            'B' => [__DIR__ . '/../../examples/cyclomatic1.php', 'B', 4],
            'Foo\\C' => [__DIR__ . '/../../examples/cyclomatic_anon.php', 'Foo\\C', 1],
            'SwitchCase' => [__DIR__ . '/../../examples/cyclomatic_full.php', 'SwitchCase', 4],
            'IfElseif' => [__DIR__ . '/../../examples/cyclomatic_full.php', 'IfElseif', 7],
            'Loops' => [__DIR__ . '/../../examples/cyclomatic_full.php', 'Loops', 5],
            'CatchIt' => [__DIR__ . '/../../examples/cyclomatic_full.php', 'CatchIt', 3],
            'Logical' => [__DIR__ . '/../../examples/cyclomatic_full.php', 'Logical', 11],
        ];
    }

    /** @return array<string,mixed> */
    public static function provideExamplesForCcn()
    {
        return [
            'A' => [__DIR__ . '/../../examples/cyclomatic1.php', 'A', 8],
            'B' => [__DIR__ . '/../../examples/cyclomatic1.php', 'B', 4],
            'Foo\\C' => [__DIR__ . '/../../examples/cyclomatic_anon.php', 'Foo\\C', 1],
            'SwitchCase' => [__DIR__ . '/../../examples/cyclomatic_full.php', 'SwitchCase', 4],
            'IfElseif' => [__DIR__ . '/../../examples/cyclomatic_full.php', 'IfElseif', 7],
            'Loops' => [__DIR__ . '/../../examples/cyclomatic_full.php', 'Loops', 5],
            'CatchIt' => [__DIR__ . '/../../examples/cyclomatic_full.php', 'CatchIt', 3],
            'Logical' => [__DIR__ . '/../../examples/cyclomatic_full.php', 'Logical', 11],
        ];
    }

    /** @return mixed[] */
    public static function provideExamplesForMaxCc()
    {
        return [
            [__DIR__ . '/../../examples/cyclomatic1.php', 'A', 6],
            [__DIR__ . '/../../examples/cyclomatic1.php', 'B', 4],
        ];
    }
}
