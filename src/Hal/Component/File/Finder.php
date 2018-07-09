<?php

/*
 * (c) Jean-François Lépine <https://twitter.com/Halleck45>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Hal\Component\File;

use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;
use RegexIterator;

/**
 * File finder
 *
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
     * @var array
     */
    private $extensions = [];

    /**
     * Subdirectories to exclude
     *
     * @var array
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
     * @param integer $flags
     */
    public function __construct(array $extensions = ['php'], array $excludedDirs = [], $flags = null)
    {
        $this->extensions = $extensions;
        $this->excludedDirs = $excludedDirs;
        $this->flags = $flags;
    }

    /**
     * Find files in path
     *
     * @param array $paths
     * @return array
     * @internal param string $path
     */
    public function fetch(array $paths)
    {
        $files = array();
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
