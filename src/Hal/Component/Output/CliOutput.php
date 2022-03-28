<?php
declare(strict_types=1);

namespace Hal\Component\Output;

use function array_keys;
use function str_replace;
use const PHP_EOL;

/**
 * Class in charge of outputting texts to the CLI.
 */
final class CliOutput implements Output
{
    private bool $quietMode = false;

    public function writeln(string $message): void
    {
        $this->write(PHP_EOL . $message);
    }

    /**
     * @param string $message
     */
    public function write(string $message): void
    {
        $replacements = [
            '<error>' => "\033[31m",
            '<success>' => "\033[32m",
            '<warning>' => "\033[33m",
            '<info>' => "\033[34m",
            '</error>' => "\033[0m",
            '</success>' => "\033[0m",
            '</warning>' => "\033[0m",
            '</info>' => "\033[0m",
        ];
        $message = str_replace(array_keys($replacements), $replacements, $message);
        $this->quietMode || print($message);
    }

    public function setQuietMode(bool $quietMode): void
    {
        $this->quietMode = $quietMode;
    }

    public function isQuiet(): bool
    {
        return $this->quietMode;
    }
}
