<?php
namespace Test\Hal\Metric\Class_;

use Hal\Metric\Class_\ClassEnumVisitor;
use Hal\Metric\Metrics;
use PhpParser\NodeTraverser;
use PhpParser\NodeVisitor\NameResolver;
use PhpParser\ParserFactory;

/**
 * @group class
 */
class ClassEnumVisitorTest extends \PHPUnit\Framework\TestCase
{

    // [__DIR__ . '/../examples/nbmethods1.php', 'A', 3, 1, 2, 7]

    /**
     * @dataProvider provideExamples
     *
     * @param string $example
     * @param string $classname
     * @param int $nbMethods
     * @param int $nbMethodsPrivate
     * @param int $nbMethodsPublic
     * @param int $nbMethodsIncludingGettersSetters
     */
    public function testMethodsAreFoundAndCountedAccordingTheirRole(
        $example,
        $classname,
        $nbMethods,
        $nbMethodsPrivate,
        $nbMethodsPublic,
        $nbMethodsIncludingGettersSetters
    ) {
        $code = file_get_contents($example);
        $this->assertNotFalse($code);

        $metrics = $this->analyzeCode($code);
        $metric = $metrics->get($classname);
        $this->assertNotNull($metric);

        $this->assertSame(
            $nbMethods,
            $metric->get('nbMethods'),
            "Expected $nbMethods methods (without getters and setters) but found {$metric->get('nbMethods')}"
        );
        $this->assertSame(
            $nbMethodsPrivate,
            $metric->get('nbMethodsPrivate'),
            "Expected $nbMethodsPrivate private methods but got {$metric->get('nbMethodsPrivate')}"
        );
        $this->assertSame(
            $nbMethodsPublic,
            $metric->get('nbMethodsPublic'),
            "Expected $nbMethodsPublic public methods but got {$metric->get('nbMethodsPublic')}"
        );
        $this->assertSame(
            $nbMethodsIncludingGettersSetters,
            $metric->get('nbMethodsIncludingGettersSetters'),
            "Expected $nbMethodsPrivate methods including getters and setters but got {$metric->get('nbMethodsIncludingGettersSetters')}"
        );
    }

    /** @return mixed[] */
    public static function provideExamples()
    {
        return [
            [__DIR__ . '/../examples/nbmethods1.php', 'A', 3, 1, 2, 7],
        ];
    }

    /**
     * @param string $code
     * @return Metrics
     */
    private function analyzeCode($code)
    {
        $metrics = new Metrics();
        $parser = (new ParserFactory())->create(ParserFactory::PREFER_PHP7);
        $traverser = new NodeTraverser();
        $traverser->addVisitor(new NameResolver());
        $traverser->addVisitor(new ClassEnumVisitor($metrics));

        $stmts = $parser->parse($code);
        $this->assertNotNull($stmts);

        $traverser->traverse($stmts);

        return $metrics;
    }

    public function testAnonymousClassIsHandledCorrectly()
    {
        $code = '<?php new class {};';
        $metrics = $this->analyzeCode($code);

        $this->assertCount(1, $metrics->all());
    }

    /**
     * @link https://github.com/phpmetrics/PhpMetrics/issues/238
     */
    public function testDynamicAttributeClassIsHandledCorrectly()
    {
        $code = '
class A {
    public function foo() {
        $reflection = new \ReflectionObject($this);

        foreach ($reflection->getProperties(\ReflectionProperty::IS_PUBLIC) as $property) {
            $return[$property->name] = $this->{$property->name};
        }
    }
}
';
        $this->analyzeCode($code);
    }


    /**
     * @link https://github.com/phpmetrics/PhpMetrics/issues/238#issuecomment-292466274
     */
    public function testDynamicAttributeClassIsHandledCorrectly2()
    {
        $code = '<?php
namespace Blackprism\CouchbaseODM\Observer;
/**
 * Interface NotifyPropertyChangedInterface
 */
interface NotifyPropertyChangedInterface
{
    /**
     * Enable tracking on object.
     */
    public function track();
    /**
     * Check if object is tracked.
     *
     * @return bool
     */
    public function isTracked(): bool;
}';
        $this->analyzeCode($code);
    }

    public function testItDoesNotMarkClassesAsAbstract()
    {
        $code = '<?php class Foo {}';
        $metrics = $this->analyzeCode($code);
        $metric = $metrics->get('Foo');
        $this->assertNotNull($metric);
        $this->assertFalse($metric->get('abstract'));
    }

    public function testItMarksAbstractClassesAsAbstract()
    {
        $code = '<?php abstract class Foo {}';
        $metrics = $this->analyzeCode($code);
        $metric = $metrics->get('Foo');
        $this->assertNotNull($metric);
        $this->assertTrue($metric->get('abstract'));
    }

    public function testItMarksInterfacesAsAbstract()
    {
        $code = '<?php interface Foo {}';
        $metrics = $this->analyzeCode($code);
        $metric = $metrics->get('Foo');
        $this->assertNotNull($metric);
        $this->assertTrue($metric->get('abstract'));
    }

    public function testItMarksTraitsAsAbstract()
    {
        $code = '<?php trait Foo {}';
        $metrics = $this->analyzeCode($code);
        $metric = $metrics->get('Foo');
        $this->assertNotNull($metric);
        $this->assertTrue($metric->get('abstract'));
    }
}
