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
     * Extensions to match (regex)
     *
     * @var string
     */
    private $extensions;

    /**
     * Subdirectories to exclude (regex)
     *
     * @var string
     */
    private $excludedDirs;

    /**
     * @param string $extensions   regex of file extensions to include
     * @param string $excludedDirs regex of directories to exclude
     */
    function __construct($extensions = 'php', $excludedDirs = '')
    {
        $this->extensions = (string) $extensions;
        $this->excludedDirs = (string) $excludedDirs;
    }

    /**
     * Find files in path
     *
     * @param string $path
     * @return array
     */
    public function find($path)
    {
        $files = array();
        if(is_dir($path)) {
            $path = rtrim($path, DIRECTORY_SEPARATOR).DIRECTORY_SEPARATOR;
            $directory = new RecursiveDirectoryIterator($path);
            $iterator = new RecursiveIteratorIterator($directory);

            $filterRegex = sprintf(
                '`^%s%s$`',
                !empty($this->excludedDirs) ? '((?!'.$this->excludedDirs.').)+' : '.+',
                '\.(' . $this->extensions . ')'
            );

            $filteredIterator = new RegexIterator(
                $iterator,
                $filterRegex,
                \RecursiveRegexIterator::GET_MATCH
            );

            foreach($filteredIterator as $file) {
                $files[] = $file[0];
            }
        } elseif(is_file($path)) {
            $files = array($path);
        }
        return $files;
    }
}
