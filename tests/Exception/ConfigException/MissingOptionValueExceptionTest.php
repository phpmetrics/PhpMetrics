<?php
declare(strict_types=1);

namespace Tests\Hal\Exception\ConfigException;

use Hal\Exception\ConfigException\MissingOptionValueException;
use PHPUnit\Framework\TestCase;

final class MissingOptionValueExceptionTest extends TestCase
{
    public function testException(): void
    {
        $exception = MissingOptionValueException::requireValue('Test');
        self::assertSame('Test option requires a value', $exception->getMessage());
    }
}
