<?php
class TokenType {

    private $operands = array(
        T_VARIABLE
        ,T_VAR
        ,T_LNUMBER
        ,T_DNUMBER
        ,T_ARRAY
        ,T_CONST
        ,T_STRING
    );
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
        ,T_ENDIF
            //    ,T_LNUMBER
            //    ,T_DNUMBER
            //    ,T_STRING
        ,T_STRING_VARNAME
            //    ,T_VARIABLE
        ,T_NUM_STRING
        ,T_INLINE_HTML
            //    ,T_CHARACTER
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
            //    ,T_CONST
        ,T_RETURN
        ,T_TRY
        ,T_CATCH
        ,T_THROW
        ,T_USE
        ,T_INSTEADOF
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
        ,T_TRAIT
        ,T_INTERFACE
        ,T_EXTENDS
        ,T_IMPLEMENTS
        ,T_OBJECT_OPERATOR
        ,T_DOUBLE_ARROW
        ,T_LIST
            //    ,T_ARRAY
        ,T_CALLABLE
        ,T_CLASS_C
        ,T_TRAIT_C
        ,T_METHOD_C
        ,T_FUNC_C
        ,T_LINE
        ,T_FILE
            //    ,T_COMMENT
            //    ,T_DOC_COMMENT
            //    ,T_OPEN_TAG
        ,T_OPEN_TAG_WITH_ECHO
            //    ,T_CLOSE_TAG
            //    ,T_WHITESPACE
            //    ,T_START_HEREDOC
            //    ,T_END_HEREDOC
            //    ,T_DOLLAR_OPEN_CURLY_BRACES
            //    ,T_CURLY_OPEN
            //    ,T_PAAMAYIM_NEKUDOTAYIM
        ,T_NAMESPACE
        ,T_NS_C
        ,T_DIR
    );

    public function isOperand(Token $token)
    {
        return in_array($token->getType(), $this->operands);
    }

    public function isOperator(Token $token)
    {
        return in_array($token->getType(), $this->operators);
    }
}
