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
class ArgumentParserTest extends \PHPUnit_Framework_TestCase {

    /**
     * @expectedException \Hal\Component\Parser\Exception\IncorrectSyntaxException
     */
    public function testSnntaxErrorIsDetected()
    {
        $code = <<<EOT
namespace Demo;
class A {
    public function foo(\$a =) {
    }
}
class B { }
EOT;

        $tokenizer = new Tokenizer();
        $tokens = $tokenizer->tokenize($code);
        $parser = new CodeParser\ArgumentsParser(new Searcher(), new NamespaceResolver($tokens));
        $parser->parse($tokens);
    }
}