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
 * @group wip
 */
class ReturnTest extends \PHPUnit_Framework_TestCase {

    public function testReturnsAreFound()
    {
        $code = <<<EOT
if(true) {
    return;
}
return new A;
EOT;

        $tokenizer = new Tokenizer();
        $tokens = $tokenizer->tokenize($code);

        $parser = new CodeParser\ReturnParser(new Searcher(), new NamespaceResolver($tokens));
        $returns = $parser->parse($tokens);
        $this->assertEquals(2, sizeof($returns));
        $this->assertEquals(Token::T_VALUE_VOID, $returns[0]->getType());
        $this->assertEquals('\\\\A', $returns[1]->getType());
    }


    /**
     * @expectedException \Hal\Component\Parser\Exception\IncorrectSyntaxException
     */
    public function testReturnsThrowsErrorWhenSyntaxErrorIsFound()
    {
        $code = <<<EOT
return new;
EOT;

        $tokenizer = new Tokenizer();
        $tokens = $tokenizer->tokenize($code);

        $parser = new CodeParser\ReturnParser(new Searcher(), new NamespaceResolver($tokens));
        $parser->parse($tokens);
    }
}