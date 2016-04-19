<?php

namespace Test;
use Hal\Component\Token\Token;
use Hal\Component\Token\Tokenizer;

/**
 * @group token
 */
class TokenizerTest extends \PHPUnit_Framework_TestCase {

    public function testICanGetTokensOfCode() {
        $code = <<<EOT
sdfdsf
<?php    class A
{

    public static function foo()
    {
        echo 'sdsfdg\'abc';
        echo "string class";

        echo "B \" B";
        echo 1;
        echo 1.2;
        echo true;
        echo false;
        echo \$a;
    }
}
?>
dsfgfdg

<?php echo 'ok'
;
EOT;

        $tokenizer = new Tokenizer();
        $tokens = $tokenizer->tokenize($code);
        
        $expected = array (
            Token::T_CLASS,
            'A',
            Token::T_BRACE_OPEN,
            Token::T_VISIBILITY_PUBLIC,
            Token::T_STATIC,
            Token::T_FUNCTION,
            'foo',
            Token::T_PARENTHESIS_OPEN,
            Token::T_PARENTHESIS_CLOSE,
            Token::T_BRACE_OPEN,
            Token::T_ECHO, Token::T_VALUE_STRING,
            Token::T_ECHO, Token::T_VALUE_STRING,
            Token::T_ECHO, Token::T_VALUE_STRING,
            Token::T_ECHO, Token::T_VALUE_INTEGER,
            //Token::T_ECHO, '1',
            Token::T_ECHO, Token::T_VALUE_FLOAT,
            //Token::T_ECHO, '1.2',
            Token::T_ECHO, Token::T_VALUE_BOOLEAN,
            Token::T_ECHO, Token::T_VALUE_BOOLEAN,
            Token::T_ECHO, Token::T_VAR,
            Token::T_BRACE_CLOSE,
            Token::T_BRACE_CLOSE,
            Token::T_ECHO,
            Token::T_VALUE_STRING
        );
        $this->assertEquals($expected, $tokens);
    }


    public function testCommentsAreDetected() {
        $code = <<<EOT
<?php
echo 'ok'; // du texte
/**
 * @param string
 */
function x() {}
;
EOT;

        $tokenizer = new Tokenizer();
        $tokens = $tokenizer->tokenize($code);

        $expected = array (
            Token::T_ECHO,
            Token::T_VALUE_STRING,
            Token::T_COMMENT,
            Token::T_COMMENT_OPEN,
            '*',
            '@param',
            'string',
            Token::T_COMMENT_CLOSE,
            Token::T_FUNCTION,
            'x',
            Token::T_PARENTHESIS_OPEN,
            Token::T_PARENTHESIS_CLOSE,
            Token::T_BRACE_OPEN,
            Token::T_BRACE_CLOSE,
        );
        $this->assertEquals($expected, $tokens);
    }

    public function testTypeCastIsDetected() {
        $code = <<<EOT
<?php
echo (string) 'ok';
;
EOT;

        $tokenizer = new Tokenizer();
        $tokens = $tokenizer->tokenize($code);

        $expected = array (
            Token::T_ECHO,
            Token::T_CAST,
            Token::T_VALUE_STRING,
        );
        $this->assertEquals($expected, $tokens);
    }

}