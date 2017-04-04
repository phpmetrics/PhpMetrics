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
 * @package Hal\Component\Issue
 */
class CliOutput implements Output
{
    /**
     * @var bool
     */
    private $quietMode = false;

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
        $this->quietMode||file_put_contents('php://stdout', $message);
        return $this;
    }

    /**
     * @inheritdoc
     */
    public function err($message)
    {
        file_put_contents('php://stderr', $message);
        return $this;
    }

    /**
     * @inheritdoc
     */
    public function clearln()
    {
        $this->writeln("\x0D");
        $this->writeln("\x1B[2K");
        return $this;
    }

    /**
     * @param boolean $quietMode
     * @return $this
     */
    public function setQuietMode($quietMode)
    {
        $this->quietMode = $quietMode;
        return $this;
    }
}

