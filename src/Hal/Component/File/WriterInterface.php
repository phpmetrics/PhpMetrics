<?php
declare(strict_types=1);

namespace Hal\Component\File;

/**
 * Contains proxy methods to PHP functions related to file system writings only.
 */
interface WriterInterface extends SystemInterface
{
    /**
     * @param string $path Path where to write the file content.
     * @param string $data Content to write in the file.
     * @return void
     */
    public function write(string $path, string $data): void;

    /**
     * @param string $path
     * @param array<array<scalar>> $data
     * @param array<scalar> $header
     * @return void
     */
    public function writeCsv(string $path, array $data, array $header): void;

    /**
     * @param string $path
     * @param mixed $data
     * @return void
     */
    public function writePrettyJson(string $path, mixed $data): void;

    /**
     * @param string $path
     * @return bool
     */
    public function isWritable(string $path): bool;

    /**
     * Copy a single file from its source to a destination folder.
     *
     * @param string $src
     * @param string $dest
     * @return void
     */
    public function copy(string $src, string $dest): void;

    /**
     * Processes a recursive copy of ech folder and file found in $src into $dest.
     *
     * @param string $src
     * @param string $dest
     * @return void
     */
    public function recursiveCopy(string $src, string $dest): void;
}
