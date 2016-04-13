<?php

namespace Test;

use Hal\Component\Parser\Helper\TypeResolver;
use Hal\Component\Parser\Token;

/**
 * @group parser
 */
class TypeResolverTest extends \PHPUnit_Framework_TestCase {

    /**
     * @dataProvider providesValues
     */
    public function testTypeIsFound($expected, $value) {
        $resolver = new TypeResolver();
        $this->assertEquals($expected, $resolver->resolve($value));
    }

    public function providesValues() {
        return array(
            array(Token::T_VALUE_STRING, 'abcd'),
            array(Token::T_VALUE_INTEGER, '1'),
            array(Token::T_VALUE_FLOAT, '2.4'),
            array(Token::T_VALUE_ARRAY, '[1,2,3]'),
            array(Token::T_VALUE_BOOLEAN, 'true'),
            array(Token::T_VALUE_BOOLEAN, 'fALSe'),
        );
    }
}