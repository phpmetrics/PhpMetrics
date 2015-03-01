<?php
namespace Test\Hal\Component\OOP\Resolver;

use Hal\Component\OOP\Resolver\NameResolver;
use PHPUnit_Framework_TestCase as TestCase;

class NameResolverTest extends TestCase
{
    /** @var NameResolver */
    private $resolver;

    protected function setUp()
    {
        $this->resolver = new NameResolver();
    }

    public function testAlreadyNamespacedClassNamesShouldNotBeChanged()
    {
        $this->assertSame('\\foo\\bar', $this->resolver->resolve('\\foo\\bar'));
    }

    public function testAliasedClassesShouldBeResolvedToItsOriginalFQCN()
    {
        $this->resolver->pushAlias((object) array('alias' => 'baz', 'name' => '\\foo\\bar'));
        $this->assertSame('\\foo\\bar', $this->resolver->resolve('baz'));
    }

    public function testUsedClassesShouldBeResolvedToItsOriginalFQCN()
    {
        $this->resolver->pushAlias((object) array('alias' => '\\foo\\bar', 'name' => '\\foo\\bar'));
        $this->assertSame('\\foo\\bar', $this->resolver->resolve('bar'));
    }

    public function testClassesThatAreNotUsedShouldBelongToTheCurrentNamespace()
    {
        $this->assertSame('\\foo\\bar', $this->resolver->resolve('bar', '\\foo'));
        $this->assertSame('\\foo\\bar', $this->resolver->resolve('bar', '\\foo\\'));
        $this->assertSame('\\baz', $this->resolver->resolve('baz'));
    }
}
