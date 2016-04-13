<?php

namespace Test;
use Hal\Component\Parser\Helper\NamespaceReplacer;
use Hal\Component\Parser\Searcher;
use Hal\Component\Parser\Token;
use Hal\Component\Parser\Tokenizer;

/**
 * @group parser
 * @group searcher
 */
class SearcherTest extends \PHPUnit_Framework_TestCase {

    public function testPreviousIsFound() {
        $tokenizer = new Tokenizer();
        $tokens = $tokenizer->tokenize('<?php class A { public function foo(){} }');
        $searcher = new Searcher();
        $position = $searcher->getPrevious($tokens, 6, Token::T_FUNCTION);
        $this->assertEquals(4, $position);
    }

    public function testPreviousIsNotFound() {
        $tokenizer = new Tokenizer();
        $tokens = $tokenizer->tokenize('<?php class A { public function foo(){} }');
        $searcher = new Searcher();
        $position = $searcher->getPrevious($tokens, 6, Token::T_STATIC);
        $this->assertFalse($position);
    }

    public function testNextIsFound() {
        $tokenizer = new Tokenizer();
        $tokens = $tokenizer->tokenize('<?php class A { public function foo(){} }');
        $searcher = new Searcher();
        $position = $searcher->getNext($tokens, 4, Token::T_PARENTHESIS_OPEN);
        $this->assertEquals(6, $position);
    }

    public function testNextIsNotFound() {
        $tokenizer = new Tokenizer();
        $tokens = $tokenizer->tokenize('<?php class A { public function foo(){} }');
        $searcher = new Searcher();
        $position = $searcher->getNext($tokens, 4, Token::T_STATIC);
        $this->assertFalse($position);
    }

    public function testClosingBraceIsFound() {
        $tokenizer = new Tokenizer();
        $tokens = $tokenizer->tokenize('<?php class A { public function foo(){} }');
        $searcher = new Searcher();
        $position = $searcher->getPositionOfClosingBrace($tokens, 2);
        $this->assertEquals(10, $position);
    }

    public function testClosingBraceIsNotFound() {
        $tokenizer = new Tokenizer();
        $tokens = $tokenizer->tokenize('<?php class A { public function foo(){} '); // syntax error
        $searcher = new Searcher();
        $position = $searcher->getPositionOfClosingBrace($tokens, 2);
        $this->assertFalse($position);
    }
}