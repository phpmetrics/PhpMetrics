<?php

/*
 * (c) Jean-François Lépine <https://twitter.com/Halleck45>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Hal\Component\Output;

/**
 * Class CliOutput
 * @package Hal\Component\Output
 */
/**
 * Class CliOutput
 * @package Hal\Component\Issue
 */
interface Output
{
    /**
     * @param $message
     * @return $this
     */
    public function writeln($message);

    /**
     * @param $message
     * @return $this
     */
    public function write($message);

    /**
     * @param $message
     * @return $this
     */
    public function err($message);

    /**
     * @return $this
     */
    public function clearln();
}

