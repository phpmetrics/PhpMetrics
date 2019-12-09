<?php

namespace Test\Hal\Metric\Package;

use Hal\Metric\Class_\ClassEnumVisitor;
use Hal\Metric\Metrics;
use Hal\Metric\Package\PackageCollectingVisitor;
use Hal\Metric\PackageMetric;
use PhpParser\NodeTraverser;
use PhpParser\NodeVisitor\NameResolver;
use PhpParser\ParserFactory;
use PHPUnit\Framework\TestCase;

/**
 * @group metric
 * @group package
 */
class PackageCollectingVisitorTest extends TestCase
{
    public function testItUsesThePackageAndTheSubpackageAnnotationAsPackageName()
    {
        $metrics = $this->analyzeCode(<<<'CODE'
<?php
namespace PackageA;

/**
 * @package PackA
 * @subpackage SubA
 */
class ClassA
{
}
CODE
        );
        $this->assertInstanceOf(PackageMetric::class, $metrics->get('PackA\\SubA\\'));
        $this->assertSame(['PackageA\\ClassA'], $metrics->get('PackA\\SubA\\')->getClasses());
        $this->assertSame('PackA\\SubA\\', $metrics->get('PackageA\\ClassA')->get('package'));
    }

    public function testItUsesThePackageAnnotationAsPackageNameIfNoSubpackageAnnotationExist()
    {
        $metrics = $this->analyzeCode(<<<'CODE'
<?php
namespace PackageA;

/**
 * @package PackA
 */
class ClassA
{
}
CODE
        );
        $this->assertInstanceOf(PackageMetric::class, $metrics->get('PackA\\'));
        $this->assertSame(['PackageA\\ClassA'], $metrics->get('PackA\\')->getClasses());
        $this->assertSame('PackA\\', $metrics->get('PackageA\\ClassA')->get('package'));
    }

    public function testItUsesTheNamespaceAsPackageNameIfNoPackageAnnotationAreAvailable()
    {
        $metrics = $this->analyzeCode(<<<'CODE'
<?php
namespace PackageA;

class ClassA
{
}
CODE
        );
        $this->assertInstanceOf(PackageMetric::class, $metrics->get('PackageA\\'));
        $this->assertSame(['PackageA\\ClassA'], $metrics->get('PackageA\\')->getClasses());
        $this->assertSame('PackageA\\', $metrics->get('PackageA\\ClassA')->get('package'));
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
        $traverser->addVisitor(new PackageCollectingVisitor($metrics));

        $stmts = $parser->parse($code);
        $traverser->traverse($stmts);

        return $metrics;
    }
}
