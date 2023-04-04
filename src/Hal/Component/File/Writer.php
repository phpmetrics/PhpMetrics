<?php
declare(strict_types=1);

namespace Hal\Component\File;

use FilesystemIterator;
use JsonException;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;
use function array_map;
use function copy;
use function fclose;
use function file_put_contents;
use function fopen;
use function fputcsv;
use function is_writable;
use function json_encode;
use function mkdir;
use const JSON_PRETTY_PRINT;
use const JSON_THROW_ON_ERROR;

/**
 * Contains proxy methods to PHP functions related to file system writings only.
 */
final class Writer extends System implements WriterInterface
{
    /**
     * {@inheritDoc}
     */
    public function write(string $path, string $data): void
    {
        file_put_contents($path, $data);
    }

    /**
     * {@inheritDoc}
     */
    public function writeCsv(string $path, array $data, array $header): void
    {
        /** @var resource $csvHandler */
        $csvHandler = fopen($path, 'wb');
        fputcsv($csvHandler, $header);
        array_map(static function (array $line) use ($csvHandler): void {
            fputcsv($csvHandler, $line);
        }, $data);
        fclose($csvHandler);
    }

    /**
     * {@inheritDoc}
     * @throws JsonException
     */
    public function writePrettyJson(string $path, mixed $data): void
    {
        $this->write($path, json_encode($data, JSON_THROW_ON_ERROR | JSON_PRETTY_PRINT));
    }

    /**
     * {@inheritDoc}
     */
    public function isWritable(string $path): bool
    {
        return is_writable($path);
    }

    /**
     * {@inheritDoc}
     */
    public function recursiveCopy(string $src, string $dest): void
    {
        $this->ensureDirectoryExists($dest);
        $fileIterator = new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator($src, FilesystemIterator::SKIP_DOTS),
            RecursiveIteratorIterator::SELF_FIRST
        );
        foreach ($fileIterator as $file) {
            /** @var RecursiveDirectoryIterator $file */

            if ($file->isDir()) {
                mkdir($dest . '/' . $file->getSubPathname());
            } else {
                copy($file->getRealPath(), $dest . '/' . $file->getSubPathname());
            }
        }
    }
}
