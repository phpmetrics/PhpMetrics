<?php
namespace Test\Hal\Metric\Class_\Structural;

use Hal\Metric\Class_\Component\MaintainabilityIndexVisitor;
use Hal\Metric\Metrics;
use PhpParser\ParserFactory;

/**
 * @group metric
 * @group mi
 * @group complex
 */
class MaintainabilityIndexVisitorTest extends \PHPUnit_Framework_TestCase {


    /**
     * @dataProvider provideValues
     */
    public function testLackOfCohesionOfMethodsIsWellCalculated($ccn, $lloc, $cloc, $volume, $mIwoC, $mi, $commentWeight)
    {
        $metrics = new Metrics();
        $prophet = $this->prophesize('Hal\Metric\ClassMetric');
        $prophet->getName()->willReturn('A');
        $prophet->get('lloc')->willReturn($lloc);
        $prophet->get('loc')->willReturn($lloc + $cloc);
        $prophet->get('ccn')->willReturn($ccn);
        $prophet->get('cloc')->willReturn($cloc);
        $prophet->get('volume')->willReturn($volume);


        // spy
        $prophet->set('mIwoC', $mIwoC)->will(function () use ($prophet) {return $prophet->reveal();})->shouldBeCalled();
        $prophet->set('mi', $mi)->will(function () use ($prophet) {return $prophet->reveal();})->shouldBeCalled();
        $prophet->set('commentWeight', $commentWeight)->will(function () use ($prophet) {return $prophet->reveal();})->shouldBeCalled();

        $class = $prophet->reveal();
        $metrics->attach($class);

        $parser = (new ParserFactory)->create(ParserFactory::PREFER_PHP7);
        $traverser = new \PhpParser\NodeTraverser();
        $traverser->addVisitor(new \PhpParser\NodeVisitor\NameResolver());
        $traverser->addVisitor(new MaintainabilityIndexVisitor($metrics));

        $code = <<<EOT
<?php class A {
    public function foo() {
    
    }
}
EOT;
        $stmts = $parser->parse($code);
        $traverser->traverse($stmts);

    }

    public function provideValues() {
        return array(
            //    CC    LLOC    CLOC        Volume      MIwoC      mi          commentWeight
            array(5     , 50    , 20       , 10         , 55.26,   92.1,      36.83 ),
            array(11    , 45   , 26       , 1777.49    , 39.7,     80.01,      40.3 )
        );
    }

}