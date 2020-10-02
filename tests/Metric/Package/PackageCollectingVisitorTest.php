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

        $metric = $metrics->get('PackA\\SubA\\');
        $this->assertInstanceOf(PackageMetric::class, $metric);
        $this->assertSame(['PackageA\\ClassA'], $metric->getClasses());

        $metric = $metrics->get('PackageA\\ClassA');
        $this->assertNotNull($metric);
        $this->assertSame('PackA\\SubA\\', $metric->get('package'));
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

        $metric =  $metrics->get('PackA\\');
        $this->assertInstanceOf(PackageMetric::class, $metric);
        $this->assertSame(['PackageA\\ClassA'], $metric->getClasses());

        $metric =  $metrics->get('PackageA\\ClassA');
        $this->assertNotNull($metric);
        $this->assertSame('PackA\\', $metric->get('package'));
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

        $metric = $metrics->get('PackageA\\ClassA');
        $this->assertNotNull($metric);
        $this->assertSame('PackageA\\', $metric->get('package'));
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
        $this->assertNotNull($stmts);

        $traverser->traverse($stmts);

        return $metrics;
    }
}
