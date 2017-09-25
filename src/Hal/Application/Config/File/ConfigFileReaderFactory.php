<?php
/**
 * (c) Jean-François Lépine <https://twitter.com/Halleck45>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Hal\Application\Config\File;

use InvalidArgumentException;

/**
 * Class ConfigFileReaderFactory
 *
 * Build a configuration file reader based on the type of file config definition.
 * Currently supported is:
 * - JSON
 * - Ini
 *
 * @package Hal\Application\Config\File
 */
class ConfigFileReaderFactory
{
    private static $allowedExtensions = [
        'json' => ConfigFileReaderJson::class,
        'ini' => ConfigFileReaderIni::class,
    ];

    /**
     * Build the right configuration file reader based on the extension of the configuration file.
     * @param string $filename Name of the configuration file we will want to read.
     * @return ConfigFileReaderInterface
     * @throws InvalidArgumentException When the configuration file to parse is not readable.
     * @throws InvalidArgumentException When the given extension is unsupported.
     */
    public static function createFromFileName($filename)
    {
        if (!\is_readable($filename)) {
            throw new InvalidArgumentException('Cannot read configuration file ' . $filename . '.');
        }

        $ext = static::checkAllowed(\pathinfo($filename, \PATHINFO_EXTENSION));

        // Builds the right object based on the extension being sure it is in the allowed extension.
        return new static::$allowedExtensions[$ext]($filename);
    }

    /**
     * Check the given extension is in the allowed list of objects to build.
     * @param string $ext The given extension to be checked.
     * @return string The extension if supported.
     * @throws InvalidArgumentException When the given extension is unsupported.
     */
    private static function checkAllowed($ext)
    {
        $allowedExtensions = \array_keys(static::$allowedExtensions);
        if (!\in_array($ext, $allowedExtensions, true)) {
            $msg = \sprintf(
                'Unsupported config file format "%s". Only supporting "%s".',
                $ext,
                \implode(', ', $allowedExtensions)
            );
            throw new InvalidArgumentException($msg);
        }

        return $ext;
    }
}
