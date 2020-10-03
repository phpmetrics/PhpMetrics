<?php

/*
 * (c) Jean-François Lépine <https://twitter.com/Halleck45>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Hal\Component\File;

use FilesystemIterator;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;
use RegexIterator;

/**
 * @author Jean-François Lépine <https://twitter.com/Halleck45>
 */
class Finder
{

    /**
     * Follow symlinks
     */
    const FOLLOW_SYMLINKS = RecursiveDirectoryIterator::FOLLOW_SYMLINKS;

    /**
     * Extensions to match
     *
     * @var string[]
     */
    private $extensions = [];

    /**
     * Subdirectories to exclude
     *
     * @var string[]
     */
    private $excludedDirs = [];

    /**
     * Flags for RecursiveDirectoryIterator
     *
     * @var integer
     */
    private $flags;

    /**
     * @param string[] $extensions   regex of file extensions to include
     * @param string[] $excludedDirs regex of directories to exclude
     * @param int $flags
     */
    public function __construct(array $extensions = ['php'], array $excludedDirs = [], $flags = -1)
    {
        $this->extensions = $extensions;
        $this->excludedDirs = $excludedDirs;
        $this->flags = $flags;
        if ($flags === -1) {
            $this->flags = FilesystemIterator::KEY_AS_PATHNAME | FilesystemIterator::CURRENT_AS_FILEINFO;
        }
    }

    /**
     * Find files in path
     *
     * @param string[] $paths
     * @return string[]
     */
    public function fetch(array $paths)
    {
        $files = [];
        foreach ($paths as $path) {
            if (is_dir($path)) {
                $path = rtrim($path, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR;
                $directory = new RecursiveDirectoryIterator($path, $this->flags);
                $iterator = new RecursiveIteratorIterator($directory);

                $filterRegex = sprintf(
                    '`^%s%s%s$`',
                    preg_quote($path, '`'),
                    !empty($this->excludedDirs) ? '((?!' . implode('|', array_map('preg_quote', $this->excludedDirs)) . ').)+' : '.+',
                    '\.(' . implode('|', $this->extensions) . ')'
                );

                $filteredIterator = new RegexIterator(
                    $iterator,
                    $filterRegex,
                    \RecursiveRegexIterator::GET_MATCH
                );

                foreach ($filteredIterator as $file) {
                    $files[] = $file[0];
                }
            } elseif (is_file($path)) {
                $files[] = $path;
            }
        }
        return $files;
    }
}
