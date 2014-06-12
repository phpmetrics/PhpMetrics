<?php
namespace Test\Hal\Component\Token;

use Hal\Component\Token\Token;
use Hal\Component\Token\TokenCollection;

/**
 * @group token
 */
class TokenCollectionTest extends \PHPUnit_Framework_TestCase {


    private $tokens;

    public function __construct() {
        $this->tokens = new TokenCollection(array(
                array(T_STRING, 'a')
            , array(T_STRING, 'b')
            , array(T_STRING, 'c')
        ));
    }

    public function testICanCountTokens() {
       $this->assertEquals(3, sizeof($this->tokens, COUNT_NORMAL));
    }

    public function testTokenCollectionIsImmutable() {
        $instance = $this->tokens->remove(0);
        $this->assertFalse($instance === $this->tokens);

        $instance = $this->tokens->replace(0, new Token(array(T_STRING, 'abc')));
        $this->assertFalse($instance === $this->tokens);
    }

    public function testICanGetTokenByIndex() {
        $this->assertInstanceOf('\Hal\Component\Token\Token', $this->tokens->get(0));
    }

    public function testICanExtractPartOfCollection() {
        $instance = $this->tokens->extract(0, 2);
        $this->assertEquals(2, $instance->count());
        $this->assertFalse($instance === $this->tokens);

    }
}