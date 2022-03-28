<?php
declare(strict_types=1);

namespace Tests\Hal\Metric\Class_\Coupling;

use Generator;
use Hal\Metric\Class_\Coupling\ExternalsVisitor;
use Hal\Metric\Helper\MetricNameGenerator;
use Hal\Metric\Helper\SimpleNodeIterator;
use Hal\Metric\Metric;
use Hal\Metric\Metrics;
use Phake;
use PhpParser\Comment\Doc;
use PhpParser\Node;
use PHPUnit\Framework\TestCase;
use function array_map;

/**
 * @phpstan-type ExpectedType array{externals: array<string>, parents: array<string>}
 */
final class ExternalsVisitorTest extends TestCase
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
        // TODO: Replace SimpleNodeIterator with a mock.
        $visitor = new ExternalsVisitor($metricsMock, new SimpleNodeIterator());

        $visitor->leaveNode($node);

        Phake::verifyNoInteraction($node);
        Phake::verifyNoInteraction($metricsMock);
    }

    /**
     * @return Generator<string, array{0: Node\Stmt\ClassLike, 1: ExpectedType}
     */
    public function provideNodesToCalculateExternals(): Generator
    {
        $node = Phake::mock(Node\Stmt\Class_::class);
        $node->extends = Phake::mock(Node\Name::class);
        Phake::when($node->extends)->__call('__toString', [])->thenReturn('A');
        $node->implements = [
            Phake::mock(Node\Name::class), // "B"
            Phake::mock(Node\Name::class), // "C"
            Phake::mock(Node\Name::class), // Ignored, as "SELF"
            Phake::mock(Node\Name::class), // Ignored, as "paRenT"
        ];
        Phake::when($node->implements[0])->__call('__toString', [])->thenReturn('B');
        Phake::when($node->implements[1])->__call('__toString', [])->thenReturn('C');
        Phake::when($node->implements[2])->__call('__toString', [])->thenReturn('SELF');
        Phake::when($node->implements[3])->__call('__toString', [])->thenReturn('paRenT');
        Phake::when($node)->__call('getMethods', [])->thenReturn([]);
        Phake::when($node)->__call('getDocComment', [])->thenReturn(null);
        $expected = ['externals' => ['A', 'B', 'C'], 'parents' => ['A']];
        yield 'Class without method' => [$node, $expected];

        $node = Phake::mock(Node\Stmt\Trait_::class);
        Phake::when($node)->__call('getMethods', [])->thenReturn([]);
        Phake::when($node)->__call('getDocComment', [])->thenReturn(null);
        $expected = ['externals' => [], 'parents' => []];
        yield 'Trait without method' => [$node, $expected];

        $node = Phake::mock(Node\Stmt\Interface_::class);
        $node->extends = [
            Phake::mock(Node\Name::class), // "A"
            Phake::mock(Node\Name::class), // Ignored, as "sElF", for expected externals
            Phake::mock(Node\Name::class), // Ignored, as "PARent", for expected externals
            Phake::mock(Node\Name::class), // "B"
        ];
        Phake::when($node->extends[0])->__call('__toString', [])->thenReturn('A');
        Phake::when($node->extends[1])->__call('__toString', [])->thenReturn('sElF');
        Phake::when($node->extends[2])->__call('__toString', [])->thenReturn('PARent');
        Phake::when($node->extends[3])->__call('__toString', [])->thenReturn('B');
        Phake::when($node)->__call('getMethods', [])->thenReturn([]);
        Phake::when($node)->__call('getDocComment', [])->thenReturn(null);
        $expected = ['externals' => ['A', 'B'], 'parents' => ['A', 'sElF', 'PARent', 'B']];
        yield 'Interface without method' => [$node, $expected];

        $node = Phake::mock(Node\Stmt\Class_::class);
        $node->extends = 'A';
        $node->implements = ['B', 'C', 'self', 'SELF', 'parent', 'PARENT', 'D'];
        Phake::when($node)->__call('getMethods', [])->thenReturn([]);
        Phake::when($node)->__call('getDocComment', [])->thenReturn(null);
        $expected = ['externals' => ['A', 'B', 'C', 'D'], 'parents' => ['A']];
        yield 'ClassLike without method having strings instead of Node\Name for externals' => [$node, $expected];

        $node = Phake::mock(Node\Stmt\Class_::class);
        $node->extends = null;
        $node->implements = [];
        Phake::when($node)->__call('getMethods', [])->thenReturn([]);
        $docComment = Phake::mock(Doc::class);
        Phake::when($docComment)->__call('getReformattedText', [])->thenReturn(
            <<<'PHPDOC'
            /**
             * @notFullyQualifiedClassName
             * @\RootClassName
             * @V (using aliases)
             * @X\SubClass (using aliases)
             * @Y\SubClass\SubSubClass
             */
            PHPDOC
        );
        Phake::when($node)->__call('getDocComment', [])->thenReturn($docComment);
        $expected = ['externals' => ['RootClassName', 'V', 'X\\SubClass', 'Y\\SubClass\\SubSubClass'], 'parents' => []];
        yield 'Class without method but PHPDoc annotations on class level' => [$node, $expected];

        $node = Phake::mock(Node\Stmt\Class_::class);
        $node->extends = null;
        $node->implements = [];
        $methods = [
            Phake::mock(Node\Stmt\ClassMethod::class), // (null): null / no doc, no sub nodes
            Phake::mock(Node\Stmt\ClassMethod::class), // (Name): Name / no doc, no sub nodes
            Phake::mock(Node\Stmt\ClassMethod::class), // (Identifier): Identifier / no doc, no sub nodes
            Phake::mock(Node\Stmt\ClassMethod::class), // (UnionType): IntersectionType / no doc, no sub nodes
            Phake::mock(Node\Stmt\ClassMethod::class), // (IntersectionType): UnionType / no doc, no sub nodes
            Phake::mock(Node\Stmt\ClassMethod::class), // (Name, Identifier, null, Name): Name / no doc, no sub nodes
            Phake::mock(Node\Stmt\ClassMethod::class), // Doc with no annotation
            Phake::mock(Node\Stmt\ClassMethod::class), // Doc with annotation but no class
            Phake::mock(Node\Stmt\ClassMethod::class), // Doc with annotation and class (use, use-aliases, no leaf)
            Phake::mock(Node\Stmt\ClassMethod::class), // [Expr->class: Name, Expr->class: null, Expr!class, !Expr]
            Phake::mock(Node\Stmt\ClassMethod::class), // (NullableType): NullableType / no doc, no sub nodes
        ];

        // (null): null / no doc, no sub nodes
        $methods[0]->returnType = null;
        $methods[0]->params = [];
        Phake::when($methods[0])->__call('getDocComment', [])->thenReturn(null);
        Phake::when($methods[0])->__call('getSubNodeNames', [])->thenReturn(['unitTestSubNodes']);
        $methods[0]->unitTestSubNodes = [];

        // (Name): Name / no doc, no sub nodes
        $methods[1]->returnType = Phake::mock(Node\Name::class);
        Phake::when($methods[1]->returnType)->__call('__toString', [])->thenReturn('A');
        $methods[1]->params = [Phake::mock(Node\Param::class)];
        $methods[1]->params[0]->type = Phake::mock(Node\Name::class);
        Phake::when($methods[1]->params[0]->type)->__call('__toString', [])->thenReturn('B');
        Phake::when($methods[1])->__call('getDocComment', [])->thenReturn(null);
        Phake::when($methods[1])->__call('getSubNodeNames', [])->thenReturn(['unitTestSubNodes']);
        $methods[1]->unitTestSubNodes = [];

        // (Identifier): Identifier / no doc, no sub nodes
        $methods[2]->returnType = Phake::mock(Node\Identifier::class);
        Phake::when($methods[2]->returnType)->__call('__toString', [])->thenReturn('void');
        $methods[2]->params = [Phake::mock(Node\Param::class)];
        $methods[2]->params[0]->type = Phake::mock(Node\Identifier::class);
        Phake::when($methods[2]->params[0]->type)->__call('__toString', [])->thenReturn('int');
        Phake::when($methods[2])->__call('getDocComment', [])->thenReturn(null);
        Phake::when($methods[2])->__call('getSubNodeNames', [])->thenReturn(['unitTestSubNodes']);
        $methods[2]->unitTestSubNodes = [];

        // (UnionType): IntersectionType / no doc, no sub nodes
        // UnionType is Name|Identifier
        // IntersectionType is Identifier|Name
        $methods[3]->returnType = Phake::mock(Node\IntersectionType::class);
        $methods[3]->returnType->types = [Phake::mock(Node\Identifier::class), Phake::mock(Node\Name::class)];
        Phake::when($methods[3]->returnType->types[1])->__call('__toString', [])->thenReturn('C');
        $methods[3]->params = [Phake::mock(Node\Param::class)];
        $methods[3]->params[0]->type = Phake::mock(Node\UnionType::class);
        $methods[3]->params[0]->type->types = [Phake::mock(Node\Name::class), Phake::mock(Node\Identifier::class)];
        Phake::when($methods[3]->params[0]->type->types[0])->__call('__toString', [])->thenReturn('D');
        Phake::when($methods[3])->__call('getDocComment', [])->thenReturn(null);
        Phake::when($methods[3])->__call('getSubNodeNames', [])->thenReturn(['unitTestSubNodes']);
        $methods[3]->unitTestSubNodes = [];

        // (IntersectionType): UnionType / no doc, no sub nodes
        // IntersectionType is Name|Identifier
        // UnionType is Identifier|Name
        $methods[4]->returnType = Phake::mock(Node\UnionType::class);
        $methods[4]->returnType->types = [Phake::mock(Node\Identifier::class), Phake::mock(Node\Name::class)];
        Phake::when($methods[4]->returnType->types[1])->__call('__toString', [])->thenReturn('E');
        $methods[4]->params = [Phake::mock(Node\Param::class)];
        $methods[4]->params[0]->type = Phake::mock(Node\IntersectionType::class);
        $methods[4]->params[0]->type->types = [Phake::mock(Node\Name::class), Phake::mock(Node\Identifier::class)];
        Phake::when($methods[4]->params[0]->type->types[0])->__call('__toString', [])->thenReturn('F');
        Phake::when($methods[4])->__call('getDocComment', [])->thenReturn(null);
        Phake::when($methods[4])->__call('getSubNodeNames', [])->thenReturn(['unitTestSubNodes']);
        $methods[4]->unitTestSubNodes = [];

        // (Name, Identifier, null, Name): Name / no doc, no sub nodes
        $methods[5]->params = [
            Phake::mock(Node\Param::class),
            Phake::mock(Node\Param::class),
            Phake::mock(Node\Param::class),
            Phake::mock(Node\Param::class),
        ];
        $methods[5]->returnType = Phake::mock(Node\Name::class);
        Phake::when($methods[5]->returnType)->__call('__toString', [])->thenReturn('G');
        $methods[5]->params[0]->type = Phake::mock(Node\Name::class);
        Phake::when($methods[5]->params[0]->type)->__call('__toString', [])->thenReturn('H');
        $methods[5]->params[1]->type = Phake::mock(Node\Identifier::class);
        $methods[5]->params[2]->type = null;
        $methods[5]->params[3]->type = Phake::mock(Node\Name::class);
        Phake::when($methods[5]->params[3]->type)->__call('__toString', [])->thenReturn('I');
        Phake::when($methods[5])->__call('getDocComment', [])->thenReturn(null);
        Phake::when($methods[5])->__call('getSubNodeNames', [])->thenReturn(['unitTestSubNodes']);
        $methods[5]->unitTestSubNodes = [];

        // Doc with no annotation
        $methods[6]->params = [];
        $methods[6]->returnType = null;
        $docCommentNoAnnotation = Phake::mock(Doc::class);
        Phake::when($docCommentNoAnnotation)->__call('getReformattedText', [])->thenReturn(
            <<<'PHPDOC'
            /**
             * This comment does not have any annotation.
             */
            PHPDOC
        );
        Phake::when($methods[6])->__call('getDocComment', [])->thenReturn($docCommentNoAnnotation);
        Phake::when($methods[6])->__call('getSubNodeNames', [])->thenReturn(['unitTestSubNodes']);
        $methods[6]->unitTestSubNodes = [];

        // Doc with annotation but no class
        $methods[7]->params = [];
        $methods[7]->returnType = null;
        $docCommentNoClassesInAnnotation = Phake::mock(Doc::class);
        Phake::when($docCommentNoClassesInAnnotation)->__call('getReformattedText', [])->thenReturn(
            <<<'PHPDOC'
            /**
             * This comment does have annotation but nothing related to classes.
             * @see Something
             * @author John Doe
             * @annotation-that-does-not-exist TRUE
             */
            PHPDOC
        );
        Phake::when($methods[7])->__call('getDocComment', [])->thenReturn($docCommentNoClassesInAnnotation);
        Phake::when($methods[7])->__call('getSubNodeNames', [])->thenReturn(['unitTestSubNodes']);
        $methods[7]->unitTestSubNodes = [];

        // Doc with annotation and class (use, use-aliases, no leaf)
        // Classes expected to be in `use` statements are "V", "W", "X", "Y", and "Z".
        $methods[8]->params = [];
        $methods[8]->returnType = null;
        $docCommentClassesInAnnotation = Phake::mock(Doc::class);
        Phake::when($docCommentClassesInAnnotation)->__call('getReformattedText', [])->thenReturn(
            <<<'PHPDOC'
            /**
             * This comment does have annotation with related to classes.
             * @V Direct usage
             * @\J Root namespace
             * @W\AA Sub class in "W" namespace.
             * @\K\AA Sub class in root namespace.
             * @X\AA\BB Sub class in sub namespace in "X" namespace
             */
            PHPDOC
        );
        Phake::when($methods[8])->__call('getDocComment', [])->thenReturn($docCommentClassesInAnnotation);
        Phake::when($methods[8])->__call('getSubNodeNames', [])->thenReturn(['unitTestSubNodes']);
        $methods[8]->unitTestSubNodes = [];

        // [Expr->class: Name, Expr->class: null, Expr!class, !Expr]
        $methods[9]->params = [];
        $methods[9]->returnType = null;
        Phake::when($methods[9])->__call('getDocComment', [])->thenReturn(null);
        Phake::when($methods[9])->__call('getSubNodeNames', [])->thenReturn(['unitTestSubNodes']);
        $methods[9]->unitTestSubNodes = [
            Phake::mock(Node\Expr::class),
            Phake::mock(Node\Expr::class),
            Phake::mock(Node\Expr::class),
            Phake::mock(Node::class),
        ];
        $methods[9]->unitTestSubNodes[0]->class = Phake::mock(Node\Name::class);
        Phake::when($methods[9]->unitTestSubNodes[0]->class)->__call('__toString', [])->thenReturn('K');
        $methods[9]->unitTestSubNodes[1]->class = null;

        // (NullableType): NullableType / no doc, no sub nodes
        $methods[10]->returnType = Phake::mock(Node\NullableType::class);
        $methods[10]->returnType->type = Phake::mock(Node\Identifier::class);
        Phake::when($methods[10]->returnType->type)->__call('__toString', [])->thenReturn('L');
        $methods[10]->params = [Phake::mock(Node\Param::class)];
        $methods[10]->params[0]->type = Phake::mock(Node\NullableType::class);
        $methods[10]->params[0]->type->type = Phake::mock(Node\Identifier::class);
        Phake::when($methods[10]->params[0]->type->type)->__call('__toString', [])->thenReturn('M');
        Phake::when($methods[10])->__call('getDocComment', [])->thenReturn(null);
        Phake::when($methods[10])->__call('getSubNodeNames', [])->thenReturn(['unitTestSubNodes']);
        $methods[10]->unitTestSubNodes = [];

        Phake::when($node)->__call('getMethods', [])->thenReturn($methods);
        Phake::when($node)->__call('getDocComment', [])->thenReturn(null);
        $expected = [
            'externals' => [
                'A', 'B', 'C', 'D', 'E', 'F', 'G', 'H', 'I', 'V', 'J', 'W\\AA', 'K\\AA', 'X\\AA\\BB', 'K', 'L', 'M'
            ],
            'parents' => []
        ];
        yield 'Class without extends nor implements but implementing methods' => [$node, $expected];
    }

    /**
     * @dataProvider provideNodesToCalculateExternals
     * @param Node\Stmt\ClassLike $node
     * @param ExpectedType $expected
     * @return void
     */
    //#[DataProvider('provideNodesToCalculateExternals')] TODO PHPUnit 10.
    public function testICanCalculateExternalsOnClass(Node\Stmt\ClassLike $node, array $expected): void
    {
        $node->namespacedName = Phake::mock(Node\Identifier::class);
        Phake::when($node->namespacedName)->__call('toString', [])->thenReturn('UnitTest@Node');
        $metricsMock = Phake::mock(Metrics::class);
        $classMetricMock = Phake::mock(Metric::class);
        $nodeName = MetricNameGenerator::getClassName($node);
        Phake::when($metricsMock)->__call('get', [$nodeName])->thenReturn($classMetricMock);

        // TODO: Replace SimpleNodeIterator with a mock.
        $visitor = new ExternalsVisitor($metricsMock, new SimpleNodeIterator());

        $this->prepareUseUse($visitor);
        $visitor->leaveNode($node);

        Phake::verify($classMetricMock)->__call('set', ['externals', $expected['externals']]);
        Phake::verify($classMetricMock)->__call('set', ['parents', $expected['parents']]);
        Phake::verify($metricsMock)->__call('get', [$nodeName]);

        Phake::verifyNoOtherInteractions($classMetricMock);
        Phake::verifyNoOtherInteractions($metricsMock);
    }

    /**
     * Prepare a storage of `UseUse` nodes used when looking at annotations usage of dependencies.
     *
     * @param ExternalsVisitor $visitor
     * @return void
     */
    private function prepareUseUse(ExternalsVisitor $visitor): void
    {
        $useUseBuilder = static function (string $name): Node\Stmt\UseUse {
            $aliasIdentifier = Phake::mock(Node\Identifier::class);
            Phake::when($aliasIdentifier)->__call('__toString', [])->thenReturn($name);
            $nameIdentifier = Phake::mock(Node\Name::class);
            Phake::when($nameIdentifier)->__call('__toString', [])->thenReturn($name);
            $useUse = Phake::mock(Node\Stmt\UseUse::class);
            Phake::when($useUse)->__call('getAlias', [])->thenReturn($aliasIdentifier);
            $useUse->name = $nameIdentifier;

            return $useUse;
        };

        $erasedUseNode = Phake::mock(Node\Stmt\Use_::class);
        $erasedUseNode->uses = array_map($useUseBuilder, ['WillBeErased', 'WillAlsoBeErased']);
        $useNodes = Phake::mock(Node\Stmt\Use_::class);
        $useNodes->uses = array_map($useUseBuilder, ['V', 'W', 'X', 'Y', 'Z']);

        $visitor->leaveNode($erasedUseNode); // Creates 2 nodes in "uses" that will be erased.
        $visitor->leaveNode(Phake::mock(Node\Stmt\Namespace_::class)); // Reset the "uses"
        $visitor->leaveNode($useNodes); // Creates 5 nodes in "uses" that will be kept (V, W, X, Y and Z).
    }
}
