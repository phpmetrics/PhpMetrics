<?php
namespace Test\Hal\Metric\Class_\Coupling;

use Hal\Metric\Class_\ClassEnumVisitor;
use Hal\Metric\Class_\Structural\SystemComplexityVisitor;
use Hal\Metric\Metrics;
use PhpParser\ParserFactory;

/**
 * @group metric
 * @group complexity
 * @group defect
 */
class SystemComplexityVisitorTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @dataProvider provideExamples
     *
     * @param string $filename
     * @param string $class
     * @param float $rdc
     * @param float $rsc
     * @param float $rsysc
     */
    public function testLackOfCohesionOfMethodsIsWellCalculated($filename, $class, $rdc, $rsc, $rsysc)
    {
        $metrics = new Metrics();

        $parser = (new ParserFactory)->create(ParserFactory::PREFER_PHP7);
        $traverser = new \PhpParser\NodeTraverser();
        $traverser->addVisitor(new \PhpParser\NodeVisitor\NameResolver());
        $traverser->addVisitor(new ClassEnumVisitor($metrics));
        $traverser->addVisitor(new SystemComplexityVisitor($metrics));

        $code = file_get_contents($filename);
        $this->assertNotFalse($code);

        $stmts = $parser->parse($code);
        $this->assertNotNull($stmts);

        $traverser->traverse($stmts);

        $metric = $metrics->get('A');
        $this->assertNotNull($metric);

        $this->assertSame($rdc, $metric->get('relativeDataComplexity'));
        $this->assertSame($rsc, $metric->get('relativeStructuralComplexity'));
        $this->assertSame($rsysc, $metric->get('relativeSystemComplexity'));
    }

    /** @return mixed[] */
    public function provideExamples()
    {
        return [
            [ __DIR__ . '/../../examples/systemcomplexity1.php', 'A', 2.5, 1.0, 3.5],
        ];
    }
}
