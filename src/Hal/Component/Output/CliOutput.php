<?php
/**
 * (c) Jean-François Lépine <https://twitter.com/Halleck45>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Hal\Component\Output;

/**
 * Class CliOutput
 *
 * Implementation of the Output interface linked to CLI feed (STDOUT/STDERR buffers).
 *
 * @package Hal\Component\Output
 */
class CliOutput implements Output
{
    /** @var string buffer name of the standard output. */
    const BUFFER_OUTPUT = 'php://stdout';

    /** @var string buffer name of the error output. */
    const BUFFER_ERROR = 'php://stderr';

    /** @var bool Defines the quite mode. If quiet, no standard output must be written, but only error outputs. */
    private $quietMode = false;

    /**
     * @inheritdoc
     */
    public function writeln($message)
    {
        return $this->write(\PHP_EOL . $message);
    }

    /**
     * @inheritdoc
     */
    public function write($message)
    {
        $this->quietMode || \file_put_contents(static::BUFFER_OUTPUT, $message);
        return $this;
    }

    /**
     * @inheritdoc
     */
    public function err($message)
    {
        \file_put_contents(static::BUFFER_ERROR, $message);
        return $this;
    }

    /**
     * @inheritdoc
     */
    public function clearln()
    {
        return $this->writeln("\x0D" . \PHP_EOL . "\x1B[2K");
    }

    /**
     * Set the quiet mode flag.
     * @param boolean $quietMode Boolean value that will be used to set the quiet mode flag.
     * @return $this
     */
    public function setQuietMode($quietMode)
    {
        $this->quietMode = $quietMode;
        return $this;
    }
}
