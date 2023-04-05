<?php
declare(strict_types=1);

namespace Tests\Hal\Component\Output;

use Generator;
use Hal\Component\Output\CliOutput;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use function ob_get_clean;
use function ob_start;
use const PHP_EOL;

final class CliOutputTest extends TestCase
{
    /**
     * @return Generator<string, array{string, string}>
     */
    public static function provideColoredOutput(): Generator
    {
        yield 'Tag "error"' => ['<error>Test</error>', "\033[31mTest\033[0m"];
        yield 'Tag "success"' => ['<success>Test</success>', "\033[32mTest\033[0m"];
        yield 'Tag "warning"' => ['<warning>Test</warning>', "\033[33mTest\033[0m"];
        yield 'Tag "info"' => ['<info>Test</info>', "\033[34mTest\033[0m"];
    }

    /**
     * @param string $message
     * @param string $expected
     */
    #[DataProvider('provideColoredOutput')]
    public function testCliOutputWithColors(string $message, string $expected): void
    {
        $output = new CliOutput();
        self::assertFalse($output->isQuiet());

        ob_start();
        $output->write($message);
        self::assertSame($expected, ob_get_clean());

        ob_start();
        $output->writeln($message);
        self::assertSame(PHP_EOL . $expected, ob_get_clean());
    }

    public function testCliOutputDefaultMode(): void
    {
        $output = new CliOutput();

        self::assertFalse($output->isQuiet());

        ob_start();
        $output->write('Test');
        self::assertSame('Test', ob_get_clean());

        ob_start();
        $output->writeln('Test with newline');
        self::assertSame(PHP_EOL . 'Test with newline', ob_get_clean());
    }

    public function testCliOutputQuietMode(): void
    {
        $output = new CliOutput();

        self::assertFalse($output->isQuiet());
        $output->setQuietMode(true);
        self::assertTrue($output->isQuiet());

        ob_start();
        $output->write('Test');
        self::assertSame('', ob_get_clean());

        ob_start();
        $output->writeln('Test with newline');
        self::assertSame('', ob_get_clean());
    }
}
