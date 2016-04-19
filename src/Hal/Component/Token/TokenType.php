<?php
namespace Hal\Component\Token;

/**
 * Class TokenType
 * @package Hal\Component\Token
 */
class TokenType {

    /**
     * @var array
     */
    private $operators = array(
        ';', '*', '/', '%', '-','+',
        '!', '!=', '%', '%=', '&', '&&', 'OR', 'AND', '||', '&=', '(', ')',
        '[', ']', '*', '*=', '+', '++', '+=', ',',
        '-', '--', '-=->', '.', '...', '/', '/=', ':', '::',
        '<', '<<', '<<=', '<=', '=', '==', '>', '>=', '>>',
        '>>=', '?', '^^=', '|', '|=', '~', ';', '=&', '“',
        '“', '‘', '‘', '#', '##',
    );

    /**
     * @param $token
     * @return bool
     */
    public function isOperator($token)
    {
        return in_array($token, $this->operators);
    }

    /**
     * @param $token
     * @return bool
     */
    public function isOperand($token)
    {
        return preg_match('!^\$\w+$!', $token)||in_array($token, [
            Token::T_CAST,
            Token::T_VALUE_ARRAY,
            Token::T_VALUE_BOOLEAN,
            Token::T_VALUE_FLOAT,
            Token::T_VALUE_INTEGER,
            Token::T_VALUE_STRING
        ]);
    }

}