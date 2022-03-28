<?php
declare(strict_types=1);

namespace Tests\Hal\Exception\ConfigException;

use Hal\Exception\ConfigException\FileDoesNotExistException;
use PHPUnit\Framework\TestCase;

final class FileDoesNotExistExceptionTest extends TestCase
{
    public function testException(): void
    {
        $exception = FileDoesNotExistException::fromConfig('Test');
        self::assertSame('Directory Test does not exist', $exception->getMessage());
    }
}
