<?php
declare(strict_types=1);

namespace Tests\Hal\Exception\GraphException;

use Hal\Component\Tree\Node;
use Hal\Exception\GraphException\TargetNodeMissingException;
use PHPUnit\Framework\TestCase;

final class TargetNodeMissingExceptionTest extends TestCase
{
    public function testException(): void
    {
        $node = new Node('Error');
        $exception = TargetNodeMissingException::inGraph($node);
        self::assertSame('The target node "Error" is not is in the graph', $exception->getMessage());
    }
}
