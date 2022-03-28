<?php
declare(strict_types=1);

namespace Tests\Hal\Exception\ConfigException;

use Hal\Exception\ConfigException\GitBinaryIsIncorrectException;
use PHPUnit\Framework\TestCase;

final class GitBinaryIsIncorrectExceptionTest extends TestCase
{
    public function testException(): void
    {
        $exception = GitBinaryIsIncorrectException::invalidCommand();
        self::assertSame('Git binary (git) incorrect', $exception->getMessage());
    }
}
