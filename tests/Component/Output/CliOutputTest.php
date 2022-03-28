<?php
declare(strict_types=1);

namespace Tests\Hal\Component\Output;

use Hal\Component\Output\CliOutput;
use PHPUnit\Framework\TestCase;
use function ob_get_clean;
use function ob_start;
use const PHP_EOL;

final class CliOutputTest extends TestCase
{
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
