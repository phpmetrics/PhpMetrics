<?php

namespace Test;
use Hal\Component\Parser\CodeParser;
use Hal\Component\Reflected\File;
use Hal\Component\Parser\Resolver\NamespaceResolver;
use Hal\Component\Parser\Searcher;
use Hal\Component\Token\Token;
use Hal\Component\Token\Tokenizer;

/**
 * @group parser
 */
class CodeParserTest extends \PHPUnit_Framework_TestCase {

    public function testICanFindClasses()
    {
        $code = <<<EOT
namespace Demo;
class A extends MyParent {
    protected static \$attr1;
    public \$attr2;
    protected static function foo(\StdClass \$arg1, \$arg2, \$arg3 = null){
        echo self::class;
    }

    public static function bar(){
        echo self::class;
    }
}
class B {
}

function function1() {}
EOT;

        $tokenizer = new Tokenizer();
        $tokens = $tokenizer->tokenize($code);

        $parser = new CodeParser(new Searcher(), new NamespaceResolver($tokens));
        $result = $parser->parse($tokens);

        // Classes
        // -------------
        $classes = $result->getClasses();

        $this->assertEquals(2, sizeof($classes));

        // first class is found
        $classA = $classes[0];
        $this->assertEquals('A', $classA->getName(), 'first class is found');
        $this->assertEquals('\\Demo\\A', $classA->getFullName(), 'namespace is provided');
        $methods = $classA->getMethods();
        $this->assertEquals(2, sizeof($methods));

        // methods are found
        $method1 = $methods['foo'];
        $this->assertEquals('foo', $method1->getName());
        $this->assertEquals(false, $method1->isPublic(), 'visibility of method is found');
        $this->assertEquals(Token::T_VISIBILITY_PROTECTED, $method1->getVisibility(), 'visibility of method is found');
        $this->assertEquals(true, $method1->isStatic(), 'static method is found');

        // parents are found
        $this->assertEquals(array('\\Demo\MyParent'), $classA->getParents());

        // arguments are found
        $arguments = $method1->getArguments();
        $this->assertEquals(3, sizeof($arguments));
        $this->assertTrue($arguments[0]->isRequired());
        $this->assertEquals('\StdClass', $arguments[0]->getType());
        $this->assertEquals('$arg1', $arguments[0]->getName());
        $this->assertTrue($arguments[1]->isRequired());
        $this->assertEquals(null, $arguments[1]->getType());
        $this->assertEquals('$arg2', $arguments[1]->getName());
        $this->assertFalse($arguments[2]->isRequired());
        $this->assertEquals(null, $arguments[2]->getType());
        $this->assertEquals('$arg3', $arguments[2]->getName());

        // attributes are found
        $attributes = $classA->getAttributes();
        $this->assertEquals(2, sizeof($attributes), 'attributes are found');
        $attr1 = $attributes[0];
        $this->assertEquals(Token::T_VISIBILITY_PROTECTED, $attr1->getVisibility());
        $this->assertEquals('$attr1', $attr1->getName());
        $this->assertTrue($attr1->isStatic());
        $this->assertFalse($attr1->isPublic());


        // Functions
        // -------------
        $functions = $result->getFunctions();
        $this->assertEquals(1, sizeof($functions));
        $function1 = $functions[0];
        $this->assertEquals('function1', $function1->getName());
    }
}