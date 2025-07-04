<?php
namespace Test\Hal\Component\Ast;

use Hal\Component\Ast\NodeTraverser;
use PhpParser\NodeTraverser as BaseTraverser;
use PHPUnit\Framework\TestCase;

class NodeTraverserTest extends TestCase
{
    public function testItCanBeInstantiated(): void
    {
        $this->assertInstanceOf(BaseTraverser::class, new NodeTraverser());
    }
}
