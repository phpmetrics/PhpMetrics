<?php

/*
 * (c) Jean-François Lépine <https://twitter.com/Halleck45>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Hal\Component\File;

use Symfony\Component\Process\Process;
use Symfony\Component\Process\ProcessUtils;

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
            if (!is_file($filename)) {
                return false;
            }

            $process = new Process('php -l ' . ProcessUtils::escapeArgument($filename));
            $process->setTimeout(null);
            $process->run();

            return $process->isSuccessful();
        }
    }
}