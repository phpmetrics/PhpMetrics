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
class ProgressBar
{
    /**
     * @var Output
     */
    private $output;

    /**
     * @var int
     */
    private $max;

    /**
     * @var int
     */
    private $current = 0;

    /**
     * @param Output $output
     * @param int $max
     */
    public function __construct(Output $output, $max)
    {
        $this->output = $output;
        $this->max = $max;
    }

    /**
     * Start progress bar
     */
    public function start()
    {
        $this->current = 0;
    }

    /**
     * Advance progress bar
     */
    public function advance()
    {
        $this->current++;

        if ($this->output->hasAnsi()) {
            $percent = round($this->current / $this->max * 100);
            $this->output->write("\x0D");
            $this->output->write("\x1B[2K");
            $this->output->write(sprintf('... %s%% ...', $percent));
        } else {
            $this->output->write('.');
        }
    }

    /**
     * Clear console
     */
    public function clear()
    {
        if ($this->output->hasAnsi()) {
            $this->output->write("\x0D");
            $this->output->write("\x1B[2K");
            $this->output->clearln();
        }
    }
}
