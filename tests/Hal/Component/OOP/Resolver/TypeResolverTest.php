<?php
namespace Test\Hal\Component\OOP\Resolver;

use Hal\Component\OOP\Resolver\NameResolver;
use Hal\Component\OOP\Resolver\TypeResolver;
use PHPUnit_Framework_TestCase as TestCase;

/**
 * @group type
 * @group resolver
 */
class TypeResolverTest extends TestCase
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
            array('$this;', TypeResolver::TYPE_FLUENT_INTERFACE),
            array('1234', TypeResolver::TYPE_INTEGER),
            array('123.1', TypeResolver::TYPE_FLOAT),
            array('array(4,5)', TypeResolver::TYPE_ARRAY),
            array('[4,5]', TypeResolver::TYPE_ARRAY),
            array('null', TypeResolver::TYPE_NULL),
            array('"abcd"', TypeResolver::TYPE_STRING),
            array('', TypeResolver::TYPE_VOID),
            array('$this->foo();', TypeResolver::TYPE_UNKNWON),
            array('new \StdClass', '\StdClass'),
            array('new \StdClass($a, $b)', '\StdClass'),
            array('new \StdClass ($a, $b)', '\StdClass'),
            array('new class implements Countable, Iterator {}', 'anonymous@class')
        );
    }
}
