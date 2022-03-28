<?php
declare(strict_types=1);

namespace Tests\Hal\Exception\GraphException;

use Hal\Exception\GraphException\NoSizeForCyclicGraphException;
use PHPUnit\Framework\TestCase;

final class NoSizeForCyclicGraphExceptionTest extends TestCase
{
    public function testException(): void
    {
        $exception = NoSizeForCyclicGraphException::incalculableSize();
        self::assertSame('Cannot get size information of cyclic graph', $exception->getMessage());
    }
}
