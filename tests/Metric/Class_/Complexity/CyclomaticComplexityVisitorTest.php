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
        $stmts = $parser->parse($code);
        $traverser->traverse($stmts);

        $this->assertSame($expectedCcn, $metrics->get($classname)->get('ccn'));
    }

    /**
     * @dataProvider provideExamplesForWmc
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
        $stmts = $parser->parse($code);
        $traverser->traverse($stmts);

        $this->assertSame($expectedWmc, $metrics->get($classname)->get('wmc'));
    }

    /**
     * @dataProvider provideExamplesForMaxCc
     */
    public function testMaximalCyclomaticComplexityOfMethodsIsWellCalculated($example, $classname, $expectedCcnMethodMax)
    {
        $metrics = new Metrics();

        $parser = (new ParserFactory)->create(ParserFactory::PREFER_PHP7);
        $traverser = new NodeTraverser();
        $traverser->addVisitor(new NameResolver());
        $traverser->addVisitor(new ClassEnumVisitor($metrics));
        $traverser->addVisitor(new CyclomaticComplexityVisitor($metrics));

        $code = file_get_contents($example);
        $stmts = $parser->parse($code);
        $traverser->traverse($stmts);

        $this->assertSame($expectedCcnMethodMax, $metrics->get($classname)->get('ccnMethodMax'));
    }

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

    public static function provideExamplesForMaxCc()
    {
        return [
            [__DIR__ . '/../../examples/cyclomatic1.php', 'A', 6],
            [__DIR__ . '/../../examples/cyclomatic1.php', 'B', 4],
        ];
    }
}
