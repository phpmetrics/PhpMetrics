<?php
namespace Test\Hal\Metric\Class_;

use Hal\Component\Ast\ParserFactoryBridge;
use Hal\Metric\Class_\ClassEnumVisitor;
use Hal\Metric\Metrics;
use PhpParser\NodeTraverser;
use PhpParser\NodeVisitor\NameResolver;
use PhpParser\ParserFactory;
use PHPUnit\Framework\Attributes\DataProvider;

/**
 * @group class
 */
class ClassEnumVisitorTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @dataProvider provideExamples
     */
    #[DataProvider('provideExamples')]
    public function testMethodsAreFoundAndCountedAccordingTheirRole(
        $example,
        $classname,
        $nbMethods,
        $nbMethodsPrivate,
        $nbMethodsPublic,
        $nbMethodsIncludingGettersSetters
    ): void {
        $code = file_get_contents($example);
        $metrics = $this->analyzeCode($code);

        $this->assertSame(
            $nbMethods,
            $metrics->get($classname)->get('nbMethods'),
            "Expected $nbMethods methods (without getters and setters) but found {$metrics->get($classname)->get('nbMethods')}"
        );
        $this->assertSame(
            $nbMethodsPrivate,
            $metrics->get($classname)->get('nbMethodsPrivate'),
            "Expected $nbMethodsPrivate private methods but got {$metrics->get($classname)->get('nbMethodsPrivate')}"
        );
        $this->assertSame(
            $nbMethodsPublic,
            $metrics->get($classname)->get('nbMethodsPublic'),
            "Expected $nbMethodsPublic public methods but got {$metrics->get($classname)->get('nbMethodsPublic')}"
        );
        $this->assertSame(
            $nbMethodsIncludingGettersSetters,
            $metrics->get($classname)->get('nbMethodsIncludingGettersSetters'),
            "Expected $nbMethodsPrivate methods including getters and setters but got {$metrics->get($classname)->get('nbMethodsIncludingGettersSetters')}"
        );
    }

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
        $parser = (new ParserFactoryBridge())->create();
        $traverser = new NodeTraverser();
        $traverser->addVisitor(new NameResolver());
        $traverser->addVisitor(new ClassEnumVisitor($metrics));

        $stmts = $parser->parse($code);
        $traverser->traverse($stmts);

        return $metrics;
    }

    public function testAnonymousClassIsHandledCorrectly(): void
    {
        $code = '<?php new class {};';
        $metrics = $this->analyzeCode($code);

        $this->assertCount(1, $metrics->all());
    }

    /**
     * @link https://github.com/phpmetrics/PhpMetrics/issues/238
     */
    public function testDynamicAttributeClassIsHandledCorrectly(): void
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
        $metrics = $this->analyzeCode($code);
        $this->assertInstanceOf(Metrics::class, $metrics);
    }


    /**
     * @link https://github.com/phpmetrics/PhpMetrics/issues/238#issuecomment-292466274
     */
    public function testDynamicAttributeClassIsHandledCorrectly2(): void
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

        $metrics = $this->analyzeCode($code);
        $this->assertInstanceOf(Metrics::class, $metrics);
    }

    public function testItDoesNotMarkClassesAsAbstract(): void
    {
        $code = '<?php class Foo {}';
        $metrics = $this->analyzeCode($code);
        $this->assertFalse($metrics->get('Foo')->get('abstract'));
    }

    public function testItMarksAbstractClassesAsAbstract(): void
    {
        $code = '<?php abstract class Foo {}';
        $metrics = $this->analyzeCode($code);
        $this->assertTrue($metrics->get('Foo')->get('abstract'));
    }

    public function testItMarksInterfacesAsAbstract(): void
    {
        $code = '<?php interface Foo {}';
        $metrics = $this->analyzeCode($code);
        $this->assertTrue($metrics->get('Foo')->get('abstract'));
    }

    public function testItMarksTraitsAsAbstract(): void
    {
        $code = '<?php trait Foo {}';
        $metrics = $this->analyzeCode($code);
        $this->assertTrue($metrics->get('Foo')->get('abstract'));
    }
}
