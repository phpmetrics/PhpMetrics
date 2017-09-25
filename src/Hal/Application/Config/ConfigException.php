<?php
/**
 * (c) Jean-François Lépine <https://twitter.com/Halleck45>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Hal\Application\Config;

use Exception;
use Throwable;

/**
 * Class ConfigException
 *
 * Define all exceptions related to the configuration initialisation.
 *
 * @package Hal\Application\Config
 */
class ConfigException extends Exception
{
    /**
     * Return a ConfigException about a missing configuration option called "files".
     * @param int $code Integer code of the exception. Defaults to 0.
     * @param Throwable|null $previous The previous throwable used for the exception chaining.
     * @return $this
     */
    public static function missingFileOption($code = 0, Throwable $previous = null)
    {
        return new static('Directory of file to parse is missing or incorrect.', $code, $previous);
    }

    /**
     * Return a ConfigException about a missing file or directory in the option called "files".
     * @param string $dir The directory or file that is not found in the file system.
     * @param int $code Integer code of the exception. Defaults to 0.
     * @param Throwable|null $previous The previous throwable used for the exception chaining.
     * @return $this
     */
    public static function missingFile($dir, $code = 0, Throwable $previous = null)
    {
        return new static(\sprintf('Directory or file %s does not exist.', $dir), $code, $previous);
    }

    /**
     * Return a ConfigException about a value that is missing for an option that requires a value.
     * @param string $option The option name that requires a value.
     * @param int $code Integer code of the exception. Defaults to 0.
     * @param Throwable|null $previous The previous throwable used for the exception chaining.
     * @return $this
     */
    public static function missingMandatoryOptionValue($option, $code = 0, Throwable $previous = null)
    {
        return new static(\sprintf('%s option requires a value.', $option), $code, $previous);
    }
}
