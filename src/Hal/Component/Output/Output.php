<?php
/**
 * (c) Jean-François Lépine <https://twitter.com/Halleck45>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Hal\Component\Output;

/**
 * Interface Output
 *
 * Contract that defines methods to write to standard and error outputs.
 *
 * @package Hal\Component\Output
 */
interface Output
{
    /**
     * Output the given message with a trailing new line.
     * @param string $message The given message.
     * @return $this
     */
    public function writeln($message);

    /**
     * Output the given message.
     * @param string $message The given message.
     * @return $this
     */
    public function write($message);

    /**
     * Output into error feed the given message.
     * @param string $message The given message.
     * @return $this
     */
    public function err($message);

    /**
     * Output characters that clean the line feed.
     * @return $this
     */
    public function clearln();

    /**
     * Returns the current or default file descriptor used to make the default output.
     * @return resource
     */
    public function getFileDescriptor();

    /**
     * Returns the current or default file descriptor used to make the error output.
     * @return resource
     */
    public function getErrorFileDescriptor();
}
