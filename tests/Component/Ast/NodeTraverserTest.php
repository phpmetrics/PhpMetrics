<?php
namespace Test\Hal\Component\Ast;

use Hal\Component\Ast\NodeTraverser;
use PhpParser\NodeTraverser as BaseTraverser;
use PHPUnit_Framework_TestCase;

class NodeTraverserTest extends PHPUnit_Framework_TestCase
{
    public function testItCanBeInstantiated()
    {
        $this->assertInstanceOf(BaseTraverser::class, new NodeTraverser());
    }
}
