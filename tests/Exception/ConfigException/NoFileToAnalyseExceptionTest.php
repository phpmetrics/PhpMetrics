<?php
declare(strict_types=1);

namespace Tests\Hal\Exception\ConfigException;

use Hal\Exception\ConfigException\NoFileToAnalyseException;
use PHPUnit\Framework\TestCase;

final class NoFileToAnalyseExceptionTest extends TestCase
{
    public function testException(): void
    {
        $exception = NoFileToAnalyseException::configHasNoFilesSet();
        self::assertSame('Directory to parse is missing or incorrect', $exception->getMessage());
    }
}
