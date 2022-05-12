<?php
declare(strict_types=1);

namespace Hal\Component\File;

use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;
use RegexIterator;
use function array_map;
use function implode;
use function is_dir;
use function is_file;
use function preg_quote;
use function rtrim;
use function sprintf;
use const DIRECTORY_SEPARATOR;

/**
 * Explode a list of folders into a list of files those folders contains, filtered by using excludes list and
 * including extensions.
 */
final class Finder implements FinderInterface
{
    private readonly string $pathFilterRegex;

    /**
     * @param array<string> $extensions List of file extensions to include when fetching files from directory.
     * @param array<string> $excludedDirs List of excluded subdirectories.
     */
    public function __construct(array $extensions, array $excludedDirs)
    {
        $pregExcludedDirs = '.+';
        if ([] !== $excludedDirs) {
            $pregExcludedDirs = '((?!' . implode('|', array_map(preg_quote(...), $excludedDirs)) . ').)+';
        }
        $pregExtensions = '\.(' . implode('|', $extensions) . ')';

        // Regular expression that needs to be filled with a path to filter all files.
        $this->pathFilterRegex = '`^%s' . $pregExcludedDirs . $pregExtensions . '$`';
    }

    /**
     * {@inheritDoc}
     */
    public function fetch(array $pathsList): array
    {
        $files = [];
        foreach ($pathsList as $path) {
            if (is_file($path)) {
                $files[] = $path;
            } elseif (is_dir($path)) {
                $path = rtrim($path, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR;

                // Each iteration produces an array of a single file matching the regular expression set in constructor.
                /** @var RegexIterator<array{string}> $filteredIterator */
                $filteredIterator = new RegexIterator(
                    new RecursiveIteratorIterator(new RecursiveDirectoryIterator($path)),
                    sprintf($this->pathFilterRegex, preg_quote($path, '`')),
                    RegexIterator::GET_MATCH
                );
                foreach ($filteredIterator as $file) {
                    $files[] = $file[0];
                }
            }
        }
        return $files;
    }
}
