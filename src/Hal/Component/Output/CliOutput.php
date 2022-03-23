<?php

/*
 * (c) Jean-François Lépine <https://twitter.com/Halleck45>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Hal\Component\Output;

/**
 * @package Hal\Component\Issue
 */
class CliOutput implements Output
{
    /**
     * @var bool
     */
    private $quietMode = false;

    /**
     * @param string $message
     * @return $this
     */
    public function writeln($message)
    {
        $this->write(PHP_EOL . $message);
        return $this;
    }

    /**
     * @param string $message
     * @return $this
     */
    public function write($message)
    {
        if (preg_match_all('!<([a-z]+)(?:=[^}]+)?>(.*?)</\\1>!', $message, $matches, PREG_SET_ORDER)) {
            list(, $type, $message) = $matches[0];
            $color = '';
            switch ($type) {
                case 'error':
                    $color = "\033[31m";
                    break;
                case 'warning':
                    $color = "\033[33m";
                    break;
                case 'success':
                    $color = "\033[32m";
                    break;
                case 'info':
                    $color = "\033[34m";
                    break;
            }

            $message = $color . $message . "\033[0m";
        }

        $this->quietMode || file_put_contents('php://stdout', $message);
        return $this;
    }

    /**
     * @param string $message
     * @return $this
     */
    public function err($message)
    {
        file_put_contents('php://stderr', $message);
        return $this;
    }

    public function clearln()
    {
        $this->writeln("\x0D");
        $this->writeln("\x1B[2K");
        return $this;
    }

    /**
     * @param bool $quietMode
     * @return $this
     */
    public function setQuietMode($quietMode)
    {
        $this->quietMode = $quietMode;
        return $this;
    }

    /**
     * @inheritDoc
     */
    public function hasAnsi()
    {
        if (DIRECTORY_SEPARATOR === '\\') {
            return
                0 >= version_compare(
                    '10.0.10586',
                    PHP_WINDOWS_VERSION_MAJOR . '.' . PHP_WINDOWS_VERSION_MINOR . '.' . PHP_WINDOWS_VERSION_BUILD
                )
                || false !== getenv('ANSICON')
                || 'ON' === getenv('ConEmuANSI')
                || 'xterm' === getenv('TERM');
        }

        return function_exists('posix_isatty') && @posix_isatty(STDOUT);
    }
}
