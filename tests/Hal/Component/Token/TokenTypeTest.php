<?php
namespace Test\Hal\Component\Token;

use Hal\Component\Token\Token;
use Hal\Component\Token\TokenType;

/**
 * @group token
 */
class TokenTypeTest extends \PHPUnit_Framework_TestCase {

    private $object;

    public function setup() {
        $this->object = new TokenType();
    }

    /**
     * @dataProvider providesTokenTypes
     */
    public function testTokenTypeDistinguishsTokens($isOperator, $isOperand, $token) {
        $this->assertEquals($isOperator, $this->object->isOperator($token));
        $this->assertEquals($isOperand, $this->object->isOperand($token));
    }

    public function providesTokenTypes() {
        return array(
            array(true, false, '&&')
            , array(true, false, '&&')
            , array(false, true, '$a')
            , array(false, true, Token::T_VALUE_STRING)
            , array(false, true, Token::T_VALUE_BOOLEAN)
            , array(false, true, Token::T_VALUE_FLOAT)
            , array(false, true, Token::T_VALUE_INTEGER)

            // operators
            , array(true, false, ';')
            , array(true, false, '*')
            , array(true, false, '#')
            , array(true, false, '[')
            , array(true, false, '>>')
            , array(true, false, '!')
            , array(true, false, '+')
            , array(true, false, '?')
            , array(true, false, '!=')
            , array(true, false, '%')
            , array(true, false, '&')
            , array(true, false, '&&')
            , array(true, false, '/')
        );
    }
}