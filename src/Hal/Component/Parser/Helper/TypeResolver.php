<?php
namespace Hal\Component\Parser\Helper;

use Hal\Component\Token\Token;

class TypeResolver
{

    /**
     * @param $value
     * @return string
     */
    public function resolve($value)
    {
        if(preg_match('!^\d+$!', $value)) {
            return Token::T_VALUE_INTEGER;
        }
        if(preg_match('!^\d+\.\d+$!', $value)) {
            return Token::T_VALUE_FLOAT;
        }
        if(preg_match('!(true|false)!i', $value)) {
            return Token::T_VALUE_BOOLEAN;
        }
        if(preg_match('!^(\[|array\()!i', $value)) {
            return Token::T_VALUE_ARRAY;
        }
        if(preg_match('!^\\\\!', $value)) {
            return Token::T_VALUE_OBJECT;
        }
        return Token::T_VALUE_STRING;
    }

    /**
     * @param $value
     * @return bool
     */
    public function isScalar($value)
    {
        return in_array($this->resolve($value), array(Token::T_VALUE_STRING, Token::T_VALUE_BOOLEAN, Token::T_VALUE_INTEGER, Token::T_VALUE_FLOAT));
    }

    /**
     * @param $value
     * @return bool
     */
    public function isObject($value)
    {
        return !$this->isScalar($value) &&Token::T_VALUE_ARRAY !== $this->resolve($value);
    }
}