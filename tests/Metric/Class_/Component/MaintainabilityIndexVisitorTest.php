<?php
namespace Test\Hal\Metric\Class_\Structural;

use Hal\Component\Ast\ParserFactoryBridge;
use Hal\Component\Ast\ParserTraverserVisitorsAssigner;
use Hal\Metric\Class_\Component\MaintainabilityIndexVisitor;
use Hal\Metric\ClassMetric;
use Hal\Metric\Metrics;
use PhpParser\ParserFactory;
use PHPUnit\Framework\Attributes\DataProvider;

/**
 * @group metric
 * @group mi
 * @group complex
 */
class MaintainabilityIndexVisitorTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @dataProvider provideValues
     */
    #[DataProvider('provideValues')]
    public function testLackOfCohesionOfMethodsIsWellCalculated($ccn, $lloc, $cloc, $volume, $mIwoC, $mi, $commentWeight): void
    {
        $metrics = new Metrics();
        $classMetrics = new ClassMetric('A');
        $classMetrics->set('lloc', $lloc);
        $classMetrics->set('loc', $lloc + $cloc);
        $classMetrics->set('ccn', $ccn);
        $classMetrics->set('cloc', $cloc);
        $classMetrics->set('volume', $volume);
        $metrics->attach($classMetrics);

        $parser = (new ParserFactoryBridge())->create();
        $traverser = new \PhpParser\NodeTraverser();
        (new ParserTraverserVisitorsAssigner())->assign($traverser, [
            new \PhpParser\NodeVisitor\NameResolver(),
            new MaintainabilityIndexVisitor($metrics)
        ]);

        $code = <<<EOT
    <?php class A {
        public function foo() {

        }
    }
EOT;
        $stmts = $parser->parse($code);
        $traverser->traverse($stmts);

        // And now, mi, mIwoC and commentWeight should be set
        $this->assertEquals($mi, $classMetrics->get('mi'));
        $this->assertEquals($mIwoC, $classMetrics->get('mIwoC'));
        $this->assertEquals($commentWeight, $classMetrics->get('commentWeight'));
    }

    public static function provideValues()
    {
        return [
            //    CC    LLOC    CLOC        Volume      MIwoC      mi          commentWeight
            [5     , 50    , 20       , 10         , 55.26,   92.1,      36.83 ],
            [11    , 45   , 26       , 1777.49    , 39.7,     80.01,      40.3 ]
        ];
    }
}
