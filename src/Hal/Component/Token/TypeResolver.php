<?php

/*
 * (c) Jean-François Lépine <https://twitter.com/Halleck45>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Hal\Component\Token;


/**
 * Type resolver
 *
 * @author Jean-François Lépine <https://twitter.com/Halleck45>
 */
class TypeResolver
{

    /**
     * Resolves type of given string
     *
     * @param $token
     * @return string
     */
    public function resolve($token)
    {

        if(strlen($token) == 0) {
            return Token::T_VALUE_VOID;
        }

        if (in_array($token, array(Token::T_VALUE_BOOLEAN, Token::T_VALUE_FLOAT, Token::T_VALUE_STRING, Token::T_VALUE_INTEGER, Token::T_VALUE_NULL))) {
            return $token;
        }

        if(preg_match('!^\d+$!', $token)) {
            return Token::T_VALUE_INTEGER;
        }

        if(preg_match('!^\d+\.\d+$!', $token)) {
            return Token::T_VALUE_FLOAT;
        }

        if('null' == $token) {
            return Token::T_VALUE_NULL;
        }

        if(preg_match('!^\$\w+$!', $token)) {
            return Token::T_VAR;
        }

        if(preg_match('!(^\[|^array\()!', $token)) {
            return Token::T_VALUE_ARRAY;
        }

        if(preg_match('!^(true|false)!', $token)) {
            return Token::T_VALUE_BOOLEAN;
        }

        if(preg_match('!^function!', $token)) {
            return Token::T_FUNCTION;
        }

        if(preg_match('!^["\']!', $token)) {
            return Token::T_VALUE_STRING;
        }

        return Token::T_VALUE_UNKNWON;
    }

}