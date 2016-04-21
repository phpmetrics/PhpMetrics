<?php

/*
 * (c) Jean-François Lépine <https://twitter.com/Halleck45>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

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
        Token::T_WHILE, Token::T_IF, Token::T_SWITCH, Token::T_CATCH, Token::T_RETURN, Token::T_FOR
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
        return preg_match('!^\$\w+$!', $token) // var
        ||preg_match('!^(\d+)$!', $token) // int
        ||preg_match('!(\.\d+)$!', $token) // float
        ||in_array($token, [
            Token::T_CAST,
            Token::T_VAR,
            Token::T_VALUE_ARRAY,
            Token::T_VALUE_BOOLEAN,
            Token::T_VALUE_FLOAT,
            Token::T_VALUE_INTEGER,
            Token::T_VALUE_STRING
        ]);
    }

}