<?php
/*
 * (c) Jean-François Lépine <https://twitter.com/Halleck45>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Hal\Component\Output;

/**
 * Class ProgressBar
 * @package Hal\Component\Output
 */
class ProgressBar
{
    /** @var Output Output instance that is used to make some output. */
    private $output;

    /** @var int Value that limit the progress bar. When the current reach this limit, the progress bar is 100%. */
    private $max;

    /** @var int Current value to be compared to the maximum to give a state for the progress bar. */
    private $current = 0;

    /**
     * ProgressBar constructor.
     * @param Output $output
     * @param int $max
     */
    public function __construct(Output $output, $max)
    {
        $this->output = $output;
        $this->max = $max;
    }

    /**
     * Start progress bar.
     * @return $this
     */
    public function start()
    {
        $this->current = 0;
        return $this;
    }

    /**
     * Advance progress bar.
     * @return $this
     */
    public function advance()
    {
        $this->current++;

        if ($this->hasAnsi()) {
            $percent = \round($this->current / $this->max * 100);
            $this->output->write("\x0D");
            $this->output->write("\x1B[2K");
            $this->output->write(\sprintf('... %s%% ...', $percent));
        } else {
            $this->output->write('.');
        }

        return $this;
    }

    /**
     * Clear console.
     * @return $this
     */
    public function clear()
    {
        if ($this->hasAnsi()) {
            $this->output->write("\x0D");
            $this->output->write("\x1B[2K");
            $this->output->clearln();
        }

        return $this;
    }

    /**
     * Detects ANSI support.
     *
     * @return bool
     */
    protected function hasAnsi()
    {
        if (\DIRECTORY_SEPARATOR === '\\') {
            return
                0 >= \version_compare(
                    '10.0.10586',
                    \PHP_WINDOWS_VERSION_MAJOR . '.' . \PHP_WINDOWS_VERSION_MINOR . '.' . \PHP_WINDOWS_VERSION_BUILD
                )
                || false !== \getenv('ANSICON')
                || 'ON' === \getenv('ConEmuANSI')
                || 'xterm' === \getenv('TERM');
        }

        return \function_exists('posix_isatty') && \posix_isatty($this->output->getFileDescriptor());
    }
}
