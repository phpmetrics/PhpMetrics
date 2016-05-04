<?php
namespace Test\Hal\Component\OOP\Resolver;
use Hal\Component\Token\Token;
use Hal\Component\Parser\Helper\TypeResolver;

/**
 * @group type
 * @group parser
 */
class TypeResolverTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @dataProvider providesTypes
     */
    public function testTypeResolvesWorks($string, $expected) {
        $resolver = new TypeResolver();
        $this->assertEquals($expected, $resolver->resolve($string));
    }

    public function providesTypes()
    {
        return array(
            array('$abc', Token::T_VAR),
            array('1234', Token::T_VALUE_INTEGER),
            array('123.1', Token::T_VALUE_FLOAT),
            array('array(4,5)', Token::T_VALUE_ARRAY),
            array('[4,5]', Token::T_VALUE_ARRAY),
            array('null', Token::T_VALUE_NULL),
            array(Token::T_VALUE_STRING, Token::T_VALUE_STRING),
            array('false', Token::T_VALUE_BOOLEAN),
            array('true', Token::T_VALUE_BOOLEAN),
            array('function', Token::T_FUNCTION),
            array('"abcd"', Token::T_VALUE_STRING),
            array('', Token::T_VALUE_VOID),
            array('$this->foo();', Token::T_VALUE_UNKNWON),
        );
    }
}
