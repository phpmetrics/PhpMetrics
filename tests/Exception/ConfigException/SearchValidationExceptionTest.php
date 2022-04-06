<?php
declare(strict_types=1);

namespace Tests\Hal\Exception\ConfigException;

use Hal\Exception\ConfigException\SearchValidationException;
use PHPUnit\Framework\TestCase;

final class SearchValidationExceptionTest extends TestCase
{
    public function testUnknownSearchKey(): void
    {
        $exception = SearchValidationException::unknownSearchKey('missing', ['list', 'of', 'allowed']);
        $expected = 'Invalid config for search "missing". Allowed keys: {list, of, allowed}';
        self::assertSame($expected, $exception->getMessage());
    }

    public function testInvalidType(): void
    {
        $exception = SearchValidationException::invalidType();
        self::assertSame('Invalid config for "type". Should be "class" or "interface"', $exception->getMessage());
    }

    public function testInvalidNameMatches(): void
    {
        $exception = SearchValidationException::invalidNameMatches();
        self::assertSame('Invalid config for "nameMatches". Should be a regex', $exception->getMessage());
    }

    public function testInvalidInstanceOf(): void
    {
        $exception = SearchValidationException::invalidInstanceOf();
        self::assertSame('Invalid config for "instanceOf". Should be an array of classnames', $exception->getMessage());
    }

    public function testInvalidUsesClasses(): void
    {
        $exception = SearchValidationException::invalidUsesClasses();
        $expected = 'Invalid config for "usesClasses". Should be an array of classnames or regexes matching classnames';
        self::assertSame($expected, $exception->getMessage());
    }

    public function testInvalidCustomMetricComparison(): void
    {
        $exception = SearchValidationException::invalidCustomMetricComparison('test');
        $expected = 'Invalid search expression for key test';
        self::assertSame($expected, $exception->getMessage());
    }
}
