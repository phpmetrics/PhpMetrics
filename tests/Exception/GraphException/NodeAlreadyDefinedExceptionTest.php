<?php
declare(strict_types=1);

namespace Tests\Hal\Exception\GraphException;

use Hal\Component\Tree\Node;
use Hal\Exception\GraphException\NodeAlreadyDefinedException;
use PHPUnit\Framework\TestCase;

final class NodeAlreadyDefinedExceptionTest extends TestCase
{
    public function testException(): void
    {
        $node = new Node('Error');
        $exception = NodeAlreadyDefinedException::inGraph($node);
        self::assertSame('Node Error is already defined', $exception->getMessage());
    }
}
