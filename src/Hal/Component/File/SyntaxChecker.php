<?php

/*
 * (c) Jean-François Lépine <https://twitter.com/Halleck45>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Hal\Component\File;

/**
 * Checks syntax of file
 *
 * @author Jean-François Lépine <https://twitter.com/Halleck45>
 */
class SyntaxChecker
{

    /**
     * Check syntax of file
     *
     * @param $filename
     * @return int
     */
    public function isCorrect($filename) {
        if(1 === version_compare('5.0.4', PHP_VERSION)) {
            return php_check_syntax($filename);
        }

        $php = 'php';

        if (0 >= version_compare('5.4.0', PHP_VERSION)) {
           $php = PHP_BINARY;
        }

        $output = shell_exec(sprintf('"%s" -l %s 2>&1', $php, escapeshellarg($filename)));
        
        return preg_match('!No syntax errors detected!', $output);
    }
}
