<?php
declare(strict_types=1);

namespace Tests\Hal\Metric\Package;

use Generator;
use Hal\Metric\ClassMetric;
use Hal\Metric\Helper\MetricNameGenerator;
use Hal\Metric\Metrics;
use Hal\Metric\Package\PackageCollectingVisitor;
use Hal\Metric\PackageMetric;
use Phake;
use PhpParser\Comment\Doc;
use PhpParser\Node;
use PhpParser\Node\Stmt\ClassLike;
use PHPUnit\Framework\TestCase;

final class PackageCollectingVisitorTest extends TestCase
{
    /**
     * Test that nothing occurs when the Node currently being traversed is not of the expected type.
     *
     * @return void
     */
    public function testNoActionIfNodeIsNotCorrectType(): void
    {
        $node = Phake::mock(Node::class);
        $metricsMock = Phake::mock(Metrics::class);
        $visitor = new PackageCollectingVisitor($metricsMock);

        $visitor->enterNode($node);
        $visitor->leaveNode($node);

        Phake::verifyNoInteraction($node);
        Phake::verifyNoInteraction($metricsMock);
    }

    /**
     * @return Generator<string, array{0: ClassLike, 1: string}>
     */
    public function provideNodesToAssociateToPackage(): Generator
    {
        $allowedNodeClasses = [
            'class' => Node\Stmt\Class_::class,
            'interface' => Node\Stmt\Interface_::class,
            'trait' => Node\Stmt\Trait_::class,
            //'enum' => Node\Stmt\Enum_::class,
        ];
        foreach ($allowedNodeClasses as $kind => $allowedNodeClass) {
            $node = Phake::mock($allowedNodeClass);
            $node->namespacedName = Phake::mock(Node\Identifier::class);
            Phake::when($node->namespacedName)->__call('toString', [])->thenReturn('UnitTest@Node:' . $kind);
            $nodeDoc = Phake::mock(Doc::class);
            Phake::when($node)->__call('getDocComment', [])->thenReturn($nodeDoc);
            Phake::when($nodeDoc)->__call('getText', [])->thenReturn(<<<DOC
                /**
                 * @package Foo
                 * @subpackage Bar
                 */
                DOC
            );
            $expected = 'Foo\\Bar\\';
            yield 'Package name from PHPDoc for ' . $kind => [$node, $expected];

            $node = Phake::mock($allowedNodeClass);
            $node->namespacedName = Phake::mock(Node\Identifier::class);
            Phake::when($node->namespacedName)->__call('toString', [])->thenReturn('UnitTest@Node:' . $kind);
            Phake::when($node)->__call('getDocComment', [])->thenReturn(null);
            $expected = 'UnitTestNamespace\\';
            yield 'Package name without PHPDoc for ' . $kind => [$node, $expected];
        }
    }

    /**
     * @dataProvider provideNodesToAssociateToPackage
     * @param ClassLike $node
     * @param string $expectedPackageName
     * @return void
     */
    //#[DataProvider('provideNodesToAssociateToPackage')] TODO: PHPUnit 10
    public function testAlreadyExistingPackageMetricIsAttached(ClassLike $node, string $expectedPackageName): void
    {
        $metricsMock = Phake::mock(Metrics::class);
        $nodeName = MetricNameGenerator::getClassName($node);
        Phake::when($metricsMock)->__call('has', [$expectedPackageName])->thenReturn(true);
        $namespaceNode = Phake::mock(Node\Stmt\Namespace_::class);
        $namespaceNode->name = Phake::mock(Node\Identifier::class);
        Phake::when($namespaceNode->name)->__call('__toString', [])->thenReturn('UnitTestNamespace');

        $packageMetric = Phake::mock(PackageMetric::class);
        Phake::when($metricsMock)->__call('get', [$expectedPackageName])->thenReturn($packageMetric);
        Phake::when($packageMetric)->__call('addClass', [$nodeName])->thenDoNothing();
        $classMetric = Phake::mock(ClassMetric::class);
        Phake::when($metricsMock)->__call('get', [$nodeName])->thenReturn($classMetric);
        Phake::when($classMetric)->__call('set', ['package', $expectedPackageName])->thenDoNothing();

        $visitor = new PackageCollectingVisitor($metricsMock);
        $visitor->enterNode($namespaceNode);
        $visitor->leaveNode($node);

        Phake::verify($classMetric)->__call('set', ['package', $expectedPackageName]);
        Phake::verifyNoOtherInteractions($classMetric);
        Phake::verify($packageMetric)->__call('addClass', [$nodeName]);
        Phake::verifyNoOtherInteractions($packageMetric);
        Phake::verify($metricsMock)->__call('has', [$expectedPackageName]);
        Phake::verify($metricsMock)->__call('get', [$expectedPackageName]);
        Phake::verify($metricsMock)->__call('get', [$nodeName]);
        Phake::verifyNoOtherInteractions($metricsMock);
    }

    /**
     * @dataProvider provideNodesToAssociateToPackage
     * @param ClassLike $node
     * @param string $expectedPackageName
     * @return void
     */
    //#[DataProvider('provideNodesToAssociateToPackage')] TODO: PHPUnit 10
    public function testMissingPackageMetricIsAttached(ClassLike $node, string $expectedPackageName): void
    {
        $metricsMock = Phake::mock(Metrics::class);
        $nodeName = MetricNameGenerator::getClassName($node);
        Phake::when($metricsMock)->__call('has', [$expectedPackageName])->thenReturn(false);
        $namespaceNode = Phake::mock(Node\Stmt\Namespace_::class);
        $namespaceNode->name = Phake::mock(Node\Identifier::class);
        Phake::when($namespaceNode->name)->__call('__toString', [])->thenReturn('UnitTestNamespace');

        $packageMetricCollector = null;
        Phake::when($metricsMock)->__call('attach', [Phake::anyParameters()])->thenReturnCallback(
            static function (PackageMetric $packageMetric) use (&$packageMetricCollector): void {
                $packageMetricCollector = $packageMetric;
            }
        );

        $packageMetric = Phake::mock(PackageMetric::class);
        Phake::when($metricsMock)->__call('get', [$expectedPackageName])->thenReturn($packageMetric);
        Phake::when($packageMetric)->__call('addClass', [$nodeName])->thenDoNothing();
        $classMetric = Phake::mock(ClassMetric::class);
        Phake::when($metricsMock)->__call('get', [$nodeName])->thenReturn($classMetric);
        Phake::when($classMetric)->__call('set', ['package', $expectedPackageName])->thenDoNothing();

        $visitor = new PackageCollectingVisitor($metricsMock);
        $visitor->enterNode($namespaceNode);
        $visitor->leaveNode($node);

        self::assertInstanceOf(PackageMetric::class, $packageMetricCollector);
        self::assertSame($expectedPackageName, $packageMetricCollector->getName());

        Phake::verify($classMetric)->__call('set', ['package', $expectedPackageName]);
        Phake::verifyNoOtherInteractions($classMetric);
        Phake::verify($packageMetric)->__call('addClass', [$nodeName]);
        Phake::verifyNoOtherInteractions($packageMetric);
        Phake::verify($metricsMock)->__call('has', [$expectedPackageName]);
        Phake::verify($metricsMock)->__call('attach', [$packageMetricCollector]);
        Phake::verify($metricsMock)->__call('get', [$expectedPackageName]);
        Phake::verify($metricsMock)->__call('get', [$nodeName]);
        Phake::verifyNoOtherInteractions($metricsMock);
    }
}
