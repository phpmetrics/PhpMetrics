<?php
declare(strict_types=1);

namespace Tests\Hal\Metric\Class_\Text;

use Generator;
use Hal\Metric\Class_\Text\LengthVisitor;
use Hal\Metric\Helper\MetricNameGenerator;
use Hal\Metric\Metric;
use Hal\Metric\Metrics;
use Phake;
use PhpParser\Node;
use PhpParser\PrettyPrinter;
use PHPUnit\Framework\TestCase;

/**
 * @phpstan-type ClassOrFunction Node\Stmt\ClassLike|Node\Stmt\Function_
 * @phpstan-type LengthMetrics array{loc: int, cloc: int, lloc: int}
 */
final class LengthVisitorTest extends TestCase
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
        $visitor = new LengthVisitor($metricsMock, Phake::mock(PrettyPrinter\Standard::class));

        $visitor->leaveNode($node);

        Phake::verifyNoInteraction($node);
        Phake::verifyNoInteraction($metricsMock);
    }

    /**
     * @return Generator<string, array{0: ClassOrFunction, 1: LengthMetrics, 2: PrettyPrinter\Standard}>
     */
    public function provideNodesToCalculateLength(): Generator
    {
        $allowedNodeClasses = [
            'class' => Node\Stmt\Class_::class,
            //'interface' => Node\Stmt\Interface_::class,
            'trait' => Node\Stmt\Trait_::class,
            'function' => Node\Stmt\Function_::class
        ];
        $prettyPrintMock = Phake::mock(PrettyPrinter\Standard::class);
        foreach ($allowedNodeClasses as $kind => $allowedNodeClass) {
            $node = Phake::mock($allowedNodeClass);
            $node->namespacedName = Phake::mock(Node\Identifier::class);
            Phake::when($node->namespacedName)->__call('toString', [])->thenReturn('UnitTest@Node:' . $kind);
            $code = <<<'PHP'
            
            PHP;
            Phake::when($prettyPrintMock)->__call('prettyPrintFile', [[$node]])->thenReturn($code);
            $expected = ['loc' => 0, 'cloc' => 0, 'lloc' => 0];
            yield 'No code into ' . $kind => [$node, $expected, $prettyPrintMock];
        }

        $node = Phake::mock(Node\Stmt\Class_::class);
        $node->namespacedName = Phake::mock(Node\Identifier::class);
        Phake::when($node->namespacedName)->__call('toString', [])->thenReturn('UnitTest@Node:Class');
        $code = <<<'PHP'
        /**
         * This is a multiline comment 
         */

        /*
         * This is also a multiline comment
         */
         
        // This is a single line comment
        
        /* This is also a single line comment */
         // This is a single line comment starting with space
        # This is a comment with old syntax
         # This is a comment with old syntax starting with space.
        #[ButThisIsAnAttributeAndNotAComment]
        $logicalLine = 'Without comment at the end';
        $logicalLine = 'With comment at the end'; // so this is mixed line
        $logicalLine = 'Another with comment at the end'; // so this is another mixed line.

        PHP;

        Phake::when($prettyPrintMock)->__call('prettyPrintFile', [[$node]])->thenReturn($code);
        $expected = ['loc' => 18, 'cloc' => 13, 'lloc' => 4];
        yield 'Code with all kind of lines' => [$node, $expected, $prettyPrintMock];
    }

    /**
     * @dataProvider provideNodesToCalculateLength
     * @param ClassOrFunction $node
     * @param LengthMetrics $expected
     * @param PrettyPrinter\Standard $prettyPrintMock
     * @return void
     */
    //#[DataProvider('provideNodesToCalculateLength')] TODO PHPUnit 10.
    public function testICanCalculateLength(
        Node\Stmt\ClassLike|Node\Stmt\Function_ $node,
        array $expected,
        PrettyPrinter\Standard $prettyPrintMock
    ): void {
        $metricsMock = Phake::mock(Metrics::class);
        $metricMock = Phake::mock(Metric::class);
        if ($node instanceof Node\Stmt\Function_) {
            $node->name = Phake::mock(Node\Identifier::class);
            Phake::when($node->name)->__call('toString', [])->thenReturn('UnitTest@Node');
            $nodeName = MetricNameGenerator::getFunctionName($node);
        } else {
            $node->namespacedName = Phake::mock(Node\Identifier::class);
            Phake::when($node->namespacedName)->__call('toString', [])->thenReturn('UnitTest@Node');
            $nodeName = MetricNameGenerator::getClassName($node);
        }
        Phake::when($metricsMock)->__call('get', [$nodeName])->thenReturn($metricMock);

        $visitor = new LengthVisitor($metricsMock, $prettyPrintMock);
        $visitor->leaveNode($node);

        Phake::verify($metricMock)->__call('set', ['loc', $expected['loc']]);
        Phake::verify($metricMock)->__call('set', ['cloc', $expected['cloc']]);
        Phake::verify($metricMock)->__call('set', ['lloc', $expected['lloc']]);
        Phake::verify($metricsMock)->__call('get', [$nodeName]);

        Phake::verifyNoOtherInteractions($metricMock);
        Phake::verifyNoOtherInteractions($metricsMock);
    }
}
