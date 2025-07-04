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
class TestOutput implements Output
{
    public $output;
    public $err;

    /**
     * @inheritdoc
     */
    public function writeln($message)
    {
        $this->write(PHP_EOL . $message);
        return $this;
    }

    /**
     * @inheritdoc
     */
    public function write($message)
    {
        $this->output .= $message;
        return $this;
    }

    /**
     * @inheritdoc
     */
    public function err($message)
    {
        $this->err .= $message;
        return $this;
    }

    /**
     * @inheritdoc
     */
    public function clearln() {}

    /**
     * @inheritdoc
     */
    public function hasAnsi()
    {
        return false;
    }
}
