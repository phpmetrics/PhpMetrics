<?php
declare(strict_types=1);

namespace Hal\Component\Output;

/**
 * Defines rules about the ways we can output texts.
 */
interface Output
{
    /**
     * Writes the given message with a new line in standard output.
     */
    public function writeln(string $message): void;

    /**
     * Writes the given message in standard output.
     */
    public function write(string $message): void;

    public function setQuietMode(bool $quietMode): void;

    public function isQuiet(): bool;
}
