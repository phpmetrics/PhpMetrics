<?php

/*
 * (c) Jean-François Lépine <https://twitter.com/Halleck45>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Hal\Component\Output;

/**
 * @package Hal\Component\Output
 */
interface Output
{
    /**
     * @param string $message
     * @return $this
     */
    public function writeln($message);

    /**
     * @param string $message
     * @return $this
     */
    public function write($message);

    /**
     * @param string $message
     * @return $this
     */
    public function err($message);

    /**
     * @return $this
     */
    public function clearln();
}
