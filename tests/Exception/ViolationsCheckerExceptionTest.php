<?php
declare(strict_types=1);

namespace Tests\Hal\Exception;

use Hal\Exception\ViolationsCheckerException;
use PHPUnit\Framework\TestCase;

final class ViolationsCheckerExceptionTest extends TestCase
{
    public function testException(): void
    {
        $exception = ViolationsCheckerException::tooManyCriticalViolations(8, 4);
        self::assertSame(
            'Failure due to too many critical violations: got 8 while 4 tolerated.',
            $exception->getMessage()
        );
    }
}
