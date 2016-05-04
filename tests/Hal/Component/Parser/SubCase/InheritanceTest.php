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
class InheritanceTest extends \PHPUnit_Framework_TestCase {


    public function testMotherIsFoundForClass()
    {
        $code = <<<EOT
namespace Demo;
class A extends Mother implements InterfaceA {}
class B {}
EOT;

        $tokenizer = new Tokenizer();
        $tokens = $tokenizer->tokenize($code);

        $parser = new CodeParser(new Searcher(), new NamespaceResolver($tokens));
        $result = $parser->parse($tokens);


        $classes = $result->getClasses();
        $this->assertEquals(2, sizeof($classes));
        $this->assertEquals(array('\\Demo\\Mother'), $classes[0]->getParents());
        $this->assertEquals(array(), $classes[1]->getParents());
    }

    public function testMotherAreFoundForInterface()
    {
        $code = <<<EOT
namespace Demo;
interface InterfaceA extends InterfaceB, InterfaceC { }
EOT;

        $tokenizer = new Tokenizer();
        $tokens = $tokenizer->tokenize($code);

        $parser = new CodeParser(new Searcher(), new NamespaceResolver($tokens));
        $result = $parser->parse($tokens);


        $classes = $result->getClasses();
        $this->assertEquals(1, sizeof($classes));
        $interface = $classes[0];
        $this->assertEquals(array('\\Demo\\InterfaceB', '\\Demo\\InterfaceC'), $interface->getParents());
    }
}