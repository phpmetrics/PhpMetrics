<?php
namespace Test\Hal\Metric\Class_;

use Hal\Metric\Class_\ClassEnumVisitor;
use Hal\Metric\Metrics;
use PhpParser\ParserFactory;

/**
 * @group class
 */
class ClassEnumVisitorTest extends \PHPUnit_Framework_TestCase
{


    /**
     * @dataProvider provideExamples
     */
    public function testMethodsAreFoundAndCountedAccordingTheirRole(
        $example,
        $classname,
        $nbMethods,
        $nbMethodsPrivate,
        $nbMethodsPublic,
        $nbMethodsIncludingGettersSetters
    ) {
        $metrics = new Metrics();

        $parser = (new ParserFactory())->create(ParserFactory::PREFER_PHP7);
        $traverser = new \PhpParser\NodeTraverser();
        $traverser->addVisitor(new \PhpParser\NodeVisitor\NameResolver());
        $traverser->addVisitor(new ClassEnumVisitor($metrics));

        $code = file_get_contents($example);
        $stmts = $parser->parse($code);
        $traverser->traverse($stmts);

        $this->assertSame($nbMethods, $metrics->get($classname)->get('nbMethods'));
        $this->assertSame($nbMethodsPrivate, $metrics->get($classname)->get('nbMethodsPrivate'));
        $this->assertSame($nbMethodsPublic, $metrics->get($classname)->get('nbMethodsPublic'));
        $this->assertSame($nbMethodsIncludingGettersSetters,
            $metrics->get($classname)->get('nbMethodsIncludingGettersSetters'));
    }

    public function provideExamples()
    {
        return [
            [__DIR__ . '/../examples/nbmethods1.php', 'A', 3, 1, 2, 7],
        ];
    }

    public function testAnonymousClassIsHandledCorrectly()
    {
        $metrics = new Metrics();

        $parser = (new ParserFactory())->create(ParserFactory::PREFER_PHP7);
        $traverser = new \PhpParser\NodeTraverser();
        $traverser->addVisitor(new \PhpParser\NodeVisitor\NameResolver());
        $traverser->addVisitor(new ClassEnumVisitor($metrics));

        $code = '<?php new class {};';
        $stmts = $parser->parse($code);
        $traverser->traverse($stmts);
    }

    /**
     * @link https://github.com/phpmetrics/PhpMetrics/issues/238
     */
    public function testDynamicAttributeClassIsHandledCorrectly()
    {
        $metrics = new Metrics();

        $parser = (new ParserFactory())->create(ParserFactory::PREFER_PHP7);
        $traverser = new \PhpParser\NodeTraverser();
        $traverser->addVisitor(new \PhpParser\NodeVisitor\NameResolver());
        $traverser->addVisitor(new ClassEnumVisitor($metrics));

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
        $stmts = $parser->parse($code);
        $traverser->traverse($stmts);
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
    }
}