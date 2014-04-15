<?php

/*
 * (c) Jean-François Lépine <https://twitter.com/Halleck45>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Hal\Component\Token;

/**
 * Determins the type of a token (operand, operator...)
 *
 * @author Jean-François Lépine <https://twitter.com/Halleck45>
 */
class TokenType {

    /**
     * Available operands
     * @var array
     */
    private $operands = array(
        // IDENTIFIER
        T_VARIABLE
    ,T_VAR
    ,T_LNUMBER
    ,T_DNUMBER
    ,T_ARRAY
    ,T_CONST
    ,T_STRING
    ,T_NUM_STRING

        // TYPENAME
    , T_INT_CAST
    , T_ARRAY_CAST
    , T_BOOL_CAST
    , T_DOUBLE_CAST
    , T_OBJECT_CAST
    , T_STRING_CAST
    , T_UNSET_CAST

    );

    /**
     * Available operators
     *
     * @var array
     */
    private $operators = array(
        // OPERATOR
        T_IS_EQUAL
    , T_AND_EQUAL
    , T_CONCAT_EQUAL
    , T_DIV_EQUAL
    , T_MINUS_EQUAL
    , T_MOD_EQUAL
    , T_MUL_EQUAL
    , T_OR_EQUAL
    , T_PLUS_EQUAL
    , T_SL_EQUAL
    , T_SR_EQUAL
    , T_XOR_EQUAL
    , T_IS_GREATER_OR_EQUAL
    , T_IS_SMALLER_OR_EQUAL
    , T_IS_NOT_EQUAL
    , T_IS_IDENTICAL
    , T_BOOLEAN_AND
    , T_BOOLEAN_AND
    , T_INC
    , T_OBJECT_OPERATOR
    , T_DOUBLE_COLON
    , T_PAAMAYIM_NEKUDOTAYIM

        // SCSPEC
    , T_STATIC
    , T_ABSTRACT

        // TYPE QUAL
    , T_FINAL

        // RESERVED
    , T_CONST
    , T_BREAK
    , T_CASE
    , T_CONTINUE
    , T_DEFAULT
    , T_DO
    , T_IF
    , T_ELSE
    , T_ELSEIF
    , T_FOR
    , T_FOREACH
    , T_GOTO
    , T_NEW
    , T_RETURN
    , T_SWITCH
    , T_WHILE
    );

    /**
     * Operators encapsuled int T_STRING
     *
     * @var array
     */
    private $operatorsStrings = array(
        ';', '*', '/', '%', '-','+'
    , '!', '!=', '%', '%=', '&', '&&', '||', '&=', '(', ')'
        /*, '{', '}'*/, '[', ']', '*', '*=', '+', '++', '+=', ','
    , '-', '--', '-=->', '.', '...', '/', '/=', ':', '::'
    , '<', '<<', '<<=', '<=', '=', '==', '>', '>=', '>>'
    , '>>=', '?', '^^=', '|', '|=', '~', ';', '=&', '“'
    , '“', '‘', '‘', '#', '##'
    );

    /**
     * To bypass
     *
     * @var array
     */
    private $byPass = array(
        '{', '}' // in PHP, these case are counted with T_IF, '(', ...
    , ')' , '('   // we count only the first (
    , ','
    );

    /**
     * Constructor
     */
    public function __construct() {
        if(version_compare(PHP_VERSION, '5.4.0') >= 0) {
            $this->operators = array_merge($this->operators, array(
                T_INSTEADOF
            , T_TRAIT_C
            ));
            $this->operands = array_merge($this->operands, array(
                T_TRAIT
            , T_CALLABLE
            ));
        }

        // performance : key will be equals to it value
        $this->operands = array_combine($this->operands, $this->operands);
        $this->operators= array_combine($this->operators, $this->operators);
        $this->operatorsStrings = array_combine($this->operatorsStrings, $this->operatorsStrings);
        $this->byPass= array_combine($this->byPass, $this->byPass);
    }

    /**
     * Check if the token is operand
     *
     * @param Token $token
     * @return boolean
     */
    public function isOperand(Token $token)
    {
        if(T_STRING == $token->getType()) {
            return !(isset($this->byPass[$token->getValue()]) ||isset($this->operatorsStrings[$token->getValue()]));
        }

        return isset($this->operands[$token->getType()]);
    }

    /**
     * Check if the token is operator
     *
     * @param Token $token
     * @return boolean
     */
    public function isOperator(Token $token)
    {
        if(T_STRING == $token->getType()) {
            if(isset($this->byPass[$token->getValue()])) {
                return false;
            }
            return isset($this->operatorsStrings[$token->getValue()]);
        }

        return isset($this->operators[$token->getType()]);
    }
}
