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
class CodeParserAbstractTest extends \PHPUnit_Framework_TestCase {

    public function testICanFindClasses()
    {
        $code = <<<EOT
namespace Demo;
abstract class A { }
class B { }
interface C {}
EOT;

        $tokenizer = new Tokenizer();
        $tokens = $tokenizer->tokenize($code);

        $parser = new CodeParser(new Searcher(), new NamespaceResolver($tokens));
        $result = $parser->parse($tokens);

        // Classes and interfaces
        // -------------
        $classes = $result->getClasses();

        $this->assertEquals(3, sizeof($classes));

        // first class is found
        $this->assertTrue($classes[0]->isAbstract());
        $this->assertFalse($classes[1]->isAbstract());
        $this->assertFalse($classes[2]->isAbstract());
        $this->assertTrue($classes[2]->isInterface());
    }
}