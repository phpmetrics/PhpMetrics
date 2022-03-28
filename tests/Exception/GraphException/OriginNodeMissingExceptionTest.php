<?php
declare(strict_types=1);

namespace Tests\Hal\Exception\GraphException;

use Hal\Component\Tree\Node;
use Hal\Exception\GraphException\OriginNodeMissingException;
use PHPUnit\Framework\TestCase;

final class OriginNodeMissingExceptionTest extends TestCase
{
    public function testException(): void
    {
        $node = new Node('Error');
        $exception = OriginNodeMissingException::inGraph($node);
        self::assertSame('The origin node "Error" is not is in the graph', $exception->getMessage());
    }
}
