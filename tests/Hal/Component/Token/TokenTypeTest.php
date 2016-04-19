<?php
namespace Test\Hal\Component\Token;

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