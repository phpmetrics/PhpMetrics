<?php

/*
 * (c) Jean-François Lépine <https://twitter.com/Halleck45>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Hal\Token;

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
        T_VARIABLE
        ,T_VAR
        ,T_LNUMBER
        ,T_DNUMBER
        ,T_ARRAY
        ,T_CONST
        ,T_STRING
    );

    /**
     * Available operators
     * @var array
     */
    private $operators = array(
        T_REQUIRE_ONCE
        ,T_REQUIRE
        ,T_EVAL
        ,T_INCLUDE_ONCE
        ,T_INCLUDE
        ,T_LOGICAL_OR
        ,T_LOGICAL_XOR
        ,T_LOGICAL_AND
        ,T_PRINT
        ,T_SR_EQUAL
        ,T_SL_EQUAL
        ,T_XOR_EQUAL
        ,T_OR_EQUAL
        ,T_AND_EQUAL
        ,T_MOD_EQUAL
        ,T_CONCAT_EQUAL
        ,T_DIV_EQUAL
        ,T_MUL_EQUAL
        ,T_MINUS_EQUAL
        ,T_PLUS_EQUAL
        ,T_BOOLEAN_OR
        ,T_BOOLEAN_AND
        ,T_IS_NOT_IDENTICAL
        ,T_IS_IDENTICAL
        ,T_IS_NOT_EQUAL
        ,T_IS_EQUAL
        ,T_IS_GREATER_OR_EQUAL
        ,T_IS_SMALLER_OR_EQUAL
        ,T_SR
        ,T_SL
        ,T_INSTANCEOF
        ,T_UNSET_CAST
        ,T_BOOL_CAST
        ,T_OBJECT_CAST
        ,T_ARRAY_CAST
        ,T_STRING_CAST
        ,T_DOUBLE_CAST
        ,T_INT_CAST
        ,T_DEC
        ,T_INC
        ,T_CLONE
        ,T_NEW
        ,T_EXIT
        ,T_IF
        ,T_ELSEIF
        ,T_ELSE
        ,T_STRING_VARNAME
        ,T_NUM_STRING
        ,T_INLINE_HTML
        ,T_BAD_CHARACTER
        ,T_ENCAPSED_AND_WHITESPACE
        ,T_CONSTANT_ENCAPSED_STRING
        ,T_ECHO
        ,T_DO
        ,T_WHILE
        ,T_ENDWHILE
        ,T_FOR
        ,T_ENDFOR
        ,T_FOREACH
        ,T_ENDFOREACH
        ,T_DECLARE
        ,T_ENDDECLARE
        ,T_AS
        ,T_SWITCH
        ,T_ENDSWITCH
        ,T_CASE
        ,T_DEFAULT
        ,T_BREAK
        ,T_CONTINUE
        ,T_GOTO
        ,T_FUNCTION
        ,T_RETURN
        ,T_TRY
        ,T_CATCH
        ,T_THROW
        ,T_USE
        ,T_GLOBAL
        ,T_PUBLIC
        ,T_PROTECTED
        ,T_PRIVATE
        ,T_FINAL
        ,T_ABSTRACT
        ,T_STATIC
        ,T_VAR
        ,T_UNSET
        ,T_ISSET
        ,T_EMPTY
        ,T_HALT_COMPILER
        ,T_CLASS
        ,T_INTERFACE
        ,T_EXTENDS
        ,T_IMPLEMENTS
        ,T_OBJECT_OPERATOR
        ,T_DOUBLE_ARROW
        ,T_LIST
        ,T_CLASS_C
        ,T_METHOD_C
        ,T_FUNC_C
        ,T_LINE
        ,T_FILE
        ,T_OPEN_TAG_WITH_ECHO
        ,T_NAMESPACE
        ,T_NS_C
        ,T_DIR
    );

    public function __construct() {
        if(version_compare(PHP_VERSION, '5.4.0')) {
            $this->operators = array_merge($this->operators, array(
                T_INSTEADOF
                , T_TRAIT
                , T_CALLABLE
                , T_TRAIT_C
            ));
        }
    }
    /**
     * Check if the token is operand
     *
     * @param Token $token
     * @return boolean
     */
    public function isOperand(Token $token)
    {
        return in_array($token->getType(), $this->operands);
    }

    /**
     * Check if the token is operator
     *
     * @param Token $token
     * @return boolean
     */
    public function isOperator(Token $token)
    {
        return in_array($token->getType(), $this->operators);
    }
}
