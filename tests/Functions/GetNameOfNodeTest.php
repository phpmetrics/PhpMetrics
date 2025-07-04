<?php

namespace Functions;

use Hal\Component\Ast\NodeTraverser;
use Hal\Component\Ast\ParserFactoryBridge;
use Hal\Component\Ast\ParserTraverserVisitorsAssigner;
use PhpParser\Node\Stmt\Class_;
use PhpParser\NodeVisitor\NameResolver;
use PHPUnit\Framework\TestCase;
use Polyfill\TestCaseCompatible;

/**
 * @group name
 */
class GetNameOfNodeTest extends TestCase
{
    use TestCaseCompatible;

    public function testNameIsCorrect(): void
    {
        $code = '<?php class Foo {}';
        $parser = (new ParserFactoryBridge())->create();
        $stmts = $parser->parse($code);

        $traverser = new NodeTraverser();
        (new ParserTraverserVisitorsAssigner())->assign($traverser, [
            new NameResolver()
        ]);
        $traverser->traverse($stmts);

        $node = $stmts[0];
        $name = getNameOfNode($node);
        $this->assertEquals('Foo', $name);
    }

    public function testNameInNamespaceIsCorrect(): void
    {
        $code = '<?php namespace Bar; class Foo {}';
        $parser = (new ParserFactoryBridge())->create();
        $stmts = $parser->parse($code);

        $traverser = new NodeTraverser();
        (new ParserTraverserVisitorsAssigner())->assign($traverser, [
            new NameResolver()
        ]);
        $traverser->traverse($stmts);

        // namespace
        $node = $stmts[0];
        $name = getNameOfNode($node);
        $this->assertEquals('Bar', $name);

        // class
        $node = $node->stmts[0];
        $this->assertInstanceOf(Class_::class, $node);
        $name = getNameOfNode($node);
        $this->assertEquals('Bar\\Foo', $name);
    }
    public function testItCanNameAnonymousClass() : void
    {
        $code = '<?php class Foo { public function bar() { return new class {}; } }';
        $parser = (new ParserFactoryBridge())->create();
        $stmts = $parser->parse($code);

        $traverser = new NodeTraverser();
        (new ParserTraverserVisitorsAssigner())->assign($traverser, [
            new NameResolver()
        ]);
        $traverser->traverse($stmts);

        // class
        $node = $stmts[0];
        $name = getNameOfNode($node);
        $this->assertEquals('Foo', $name);

        // anonymous class
        $node = $node->stmts[0]->stmts[0]->expr->class;

        if(method_exists($this, 'assertMatchesRegularExpression')) {
            $this->assertMatchesRegularExpression('/^anonymous/', getNameOfNode($node));
        } else {
            $this->assertRegExp('/^anonymous/', getNameOfNode($node));;
        }
    }
}
