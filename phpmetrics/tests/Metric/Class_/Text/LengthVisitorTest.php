<?php
namespace Test\Hal\Metric\Class_\Structural;

use Hal\Metric\Class_\ClassEnumVisitor;
use Hal\Metric\Class_\Text\LengthVisitor;
use Hal\Metric\Metrics;
use PhpParser\ParserFactory;

/**
 * @group loc
 * @group metric
 */
class LengthVisitorTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @dataProvider provideExamples
     *
     * @param string $example
     * @param string $functionName
     * @param int $loc
     * @param int $lloc
     * @param int $cloc
     */
    public function testLineCountsAreWellCalculated($example, $functionName, $loc, $lloc, $cloc)
    {
        $metrics = new Metrics();

        $parser = (new ParserFactory)->create(ParserFactory::PREFER_PHP7);
        $traverser = new \PhpParser\NodeTraverser();
        $traverser->addVisitor(new \PhpParser\NodeVisitor\NameResolver());
        $traverser->addVisitor(new ClassEnumVisitor($metrics));
        $traverser->addVisitor(new LengthVisitor($metrics));

        $code = file_get_contents($example);
        $this->assertNotFalse($code);

        $stmts = $parser->parse($code);
        $this->assertNotNull($stmts);

        $traverser->traverse($stmts);

        $metric = $metrics->get($functionName);
        $this->assertNotNull($metric);

        $this->assertEquals($lloc, $metric->get('lloc'));
        $this->assertEquals($cloc, $metric->get('cloc'));
        $this->assertEquals($loc, $metric->get('loc'));
    }

    /** @return mixed[] */
    public function provideExamples()
    {
        return [
            [ __DIR__ . '/../../examples/loc1.php', 'A', 21, 13, 8],
        ];
    }
}
