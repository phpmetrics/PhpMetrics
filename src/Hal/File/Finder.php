<?php

/*
 * (c) Jean-François Lépine <https://twitter.com/Halleck45>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Hal\File;

/**
 * File finder
 *
 * @author Jean-François Lépine <https://twitter.com/Halleck45>
 */
class Finder
{

    /**
     * Extensions to match
     *
     * @var string
     */
    private $extensions;

    /**
     * @param string $extensions regex
     */
    function __construct($extensions = 'php')
    {
        $this->extensions = (string) $extensions;
    }

    /**
     * Find files in path
     *
     * @param $path
     * @return array
     */
    public function find($path) {
        $files = array();
        if(is_dir($path)) {
            $path = rtrim($path, DIRECTORY_SEPARATOR).DIRECTORY_SEPARATOR;
            $directory = new \RecursiveDirectoryIterator($path);
            $iterator = new \RecursiveIteratorIterator($directory);
            $regex = new \RegexIterator($iterator, '/^.+\.('. $this->extensions .')$/i', \RecursiveRegexIterator::GET_MATCH);
            foreach($regex as $file) {
                $files[] = $file[0];
            }
        } elseif(is_file($path)) {
            $files = array($path);
        }
        return $files;
    }
}