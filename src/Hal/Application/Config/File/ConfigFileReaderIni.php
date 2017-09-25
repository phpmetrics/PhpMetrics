<?php
/**
 * (c) Jean-François Lépine <https://twitter.com/Halleck45>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Hal\Application\Config\File;

use Hal\Application\Config\Config;
use InvalidArgumentException;

/**
 * Class ConfigFileReaderIni
 *
 * Read an Ini configuration file in order to set all file configuration in the final configuration object to use in the
 * application.
 *
 * @package Hal\Application\Config\File
 */
class ConfigFileReaderIni implements ConfigFileReaderInterface
{
    use TraitFileName;

    /**
     * Read the Ini configuration file and set its content in the config object sent in argument.
     * @param Config $config The config object to populate with the Ini configuration file content.
     * @return $this
     * @throws InvalidArgumentException When PHP can not parse ini configuration file content.
     */
    public function read(Config $config)
    {
        if (false === ($options = \parse_ini_file($this->filename))) {
            throw new InvalidArgumentException('Cannot parse configuration file ' . $this->filename . '.');
        }

        $config->fromArray($options);
        return $this;
    }
}
