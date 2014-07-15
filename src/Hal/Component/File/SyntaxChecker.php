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
        } else {
            // Try to find the best location for PHP binary (cf. https://gist.github.com/krismas/92e3e31674dbcfd7ba83)
            $phpbin = ((isset($_SERVER['_']) && $_SERVER['_']) ? $_SERVER['_'] : (defined(PHP_BINARY) ? PHP_BINARY : (defined(PHP_BINDIR) ? PHP_BINDIR.'/php' : preg_replace(':/lib$:', '/bin/php', PHP_CONFIG_FILE_PATH))));
            $phpbin = (is_executable($phpbin) ? $phpbin : 'php');
            $output = shell_exec(sprintf($phpbin.' -l %s 2>&1', escapeshellarg($filename)));
            return preg_match('!No syntax errors detected!', $output);
        }
    }
}