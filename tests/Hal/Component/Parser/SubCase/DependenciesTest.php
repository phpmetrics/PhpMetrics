<?php

namespace Test;
use Hal\Component\Parser\CodeParser;
use Hal\Component\Reflected\File;
use Hal\Component\Parser\Resolver\NamespaceResolver;
use Hal\Component\Parser\Searcher;
use Hal\Component\Parser\Token;
use Hal\Component\Parser\Tokenizer;

/**
 * @group parser
 */
class DependenciesTest extends \PHPUnit_Framework_TestCase {

    public function testStaticCallsAreFound()
    {
        $code = <<<EOT
namespace Demo;
class A {
    public function foo() {
        B::bar();
        self::bar();
        parent::foo();
    }
}
class B { }
EOT;

        $tokenizer = new Tokenizer();
        $tokens = $tokenizer->tokenize($code);

        $parser = new CodeParser(new Searcher(), new NamespaceResolver($tokens));
        $result = $parser->parse($tokens);


        $classes = $result->getClasses();
        $this->assertEquals(2, sizeof($classes));
        $classA = $classes[0];
        $methods = $classA->getMethods();
        $this->assertEquals(1, sizeof($methods));

        // Calls
        // -------------
        $calls = $methods[0]->getCalls();
        $this->assertEquals(3, sizeof($calls));
        $this->assertEquals('\Demo\B', $calls[0]->getType());
        $this->assertEquals('bar', $calls[0]->getMethodName());
        $this->assertTrue($calls[0]->isStatic());
        $this->assertFalse($calls[0]->isParent());
        $this->assertFalse($calls[0]->isItself());

        $this->assertTrue($calls[1]->isStatic());
        $this->assertFalse($calls[1]->isParent());
        $this->assertTrue($calls[1]->isItself());

        $this->assertTrue($calls[2]->isStatic());
        $this->assertTrue($calls[2]->isParent());
        $this->assertFalse($calls[2]->isItself());
    }

    public function testInstanciedCallsAreFound()
    {
        $code = <<<EOT
namespace Demo;
class A {
    public function foo() {
         \$v = new B;
        \$v->baz();
        (new C)->baz();
    }
}
class B { }
EOT;
        $tokenizer = new Tokenizer();
        $tokens = $tokenizer->tokenize($code);

        $parser = new CodeParser(new Searcher(), new NamespaceResolver($tokens));
        $result = $parser->parse($tokens);


        $classes = $result->getClasses();
        $this->assertEquals(2, sizeof($classes));
        $classA = $classes[0];
        $methods = $classA->getMethods();
        $this->assertEquals(1, sizeof($methods));

        // Dependencies
        // -------------
        $dependencies = $methods[0]->getCalls();
        $this->assertEquals(2, sizeof($dependencies));
        $this->assertEquals('\Demo\B', $dependencies[0]->getType());
        $this->assertEquals('\Demo\C', $dependencies[1]->getType());
    }

    public function testTypedReturnOfPhp7AreFound() {
        $code = <<<EOT
namespace My;
class Class1 {
    public function foo(): Class2 {
    }
}
EOT;

        $tokenizer = new Tokenizer();
        $tokens = $tokenizer->tokenize($code);

        $parser = new CodeParser(new Searcher(), new NamespaceResolver($tokens));
        $result = $parser->parse($tokens);


        $classes = $result->getClasses();
        $this->assertEquals(1, sizeof($classes));
        $classA = $classes[0];
        $methods = $classA->getMethods();
        $this->assertEquals(1, sizeof($methods));

        // Returns
        // -------------
        $returns = $methods[0]->getReturns();
        $this->assertEquals(1, sizeof($returns));
        $this->assertEquals('\\My\\Class2', $returns[0]->getType());
    }

    public function testReturnsInCodeAreFound() {
        $code = <<<EOT
namespace My;
class Class1 {
    public function foo(){
        if(true) {
            return new \B;
        } else {
            return new Class2;
        }
    }
}
EOT;

        $tokenizer = new Tokenizer();
        $tokens = $tokenizer->tokenize($code);

        $parser = new CodeParser(new Searcher(), new NamespaceResolver($tokens));
        $result = $parser->parse($tokens);


        $classes = $result->getClasses();
        $this->assertEquals(1, sizeof($classes));
        $classA = $classes[0];
        $methods = $classA->getMethods();
        $this->assertEquals(1, sizeof($methods));

        // Returns
        // -------------
        $returns = $methods[0]->getReturns();
        $this->assertEquals(2, sizeof($returns));
        $this->assertEquals('\\B', $returns[0]->getType());
        $this->assertEquals('\\My\\Class2', $returns[1]->getType());
    }


    public function testMixedDependenciesAreFound()
    {
        $code = <<<EOT
namespace Demo;
class A {
    public function foo(): \ReturnedValue {
         \$v = new B;
        (new C)->baz();
        \D::foo();
    }
}
EOT;
        $tokenizer = new Tokenizer();
        $tokens = $tokenizer->tokenize($code);

        $parser = new CodeParser(new Searcher(), new NamespaceResolver($tokens));
        $result = $parser->parse($tokens);


        $classes = $result->getClasses();
        $this->assertEquals(1, sizeof($classes));
        $classA = $classes[0];


        $expected = array(
            '\\ReturnedValue',
            '\\Demo\B',
            '\\Demo\C',
            '\\D',
        );
        $this->assertEquals($expected, $classA->getDependencies());

    }
}