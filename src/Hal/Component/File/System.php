<?php
declare(strict_types=1);

namespace Hal\Component\File;

use function file_exists;
use function glob;
use function mkdir;

/**
 * Contains proxy methods to PHP functions related to file system actions, but without adding/editing/deleting files.
 */
class System implements SystemInterface
{
    /**
     *{@inheritDoc}
     */
    public function exists(string $path): bool
    {
        return file_exists($path);
    }

    /**
     *{@inheritDoc}
     */
    public function ensureDirectoryExists(string $path): void
    {
        if (!$this->exists($path)) {
            mkdir($path, 0o755, true);
        }
    }

    /**
     * {@inheritDoc}
     */
    public function glob(string $pattern): array|false
    {
        return glob($pattern);
    }
}
