<?php
/*
 * (c) Jean-François Lépine <https://twitter.com/Halleck45>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Hal\Component\Output;

/**
 * Class TestOutput
 * @package Hal\Component\Output
 */
class TestOutput implements Output
{
    /** @var string Content of the output message when written from the default buffer. */
    public $output;

    /** @var string Content of the error message when written from the error buffer. */
    public $err;

    /**
     * @inheritdoc
     */
    public function writeln($message)
    {
        $this->write(\PHP_EOL . $message);
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
    public function clearln()
    {
    }

    /**
     * @inheritdoc
     */
    public function getFileDescriptor()
    {
        return null;
    }

    /**
     * @inheritdoc
     */
    public function getErrorFileDescriptor()
    {
        return null;
    }
}
