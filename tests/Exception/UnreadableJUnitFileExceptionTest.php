<?php
declare(strict_types=1);

namespace Tests\Hal\Exception;

use Hal\Exception\UnreadableJUnitFileException;
use PHPUnit\Framework\TestCase;

final class UnreadableJUnitFileExceptionTest extends TestCase
{
    public function testException(): void
    {
        $exception = UnreadableJUnitFileException::noPermission('reports/junit.xml');
        self::assertSame('JUnit report "reports/junit.xml" cannot be read.', $exception->getMessage());
    }
}
