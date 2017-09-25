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
use RecursiveArrayIterator;
use RecursiveIteratorIterator;

/**
 * Class ConfigFileReaderJson
 *
 * Read a JSON configuration file in order to set all file configuration in the final configuration object to use in the
 * application.
 *
 * @package Hal\Application\Config\File
 */
class ConfigFileReaderJson implements ConfigFileReaderInterface
{
    use TraitFileName;

    /**
     * Read the JSON configuration file and set its content in the config object sent in argument.
     * @param Config $config The config object to populate with the JSON configuration file content.
     * @return $this
     * @throws InvalidArgumentException When the configuration file content is not accessible.
     * @throws InvalidArgumentException When the configuration file content is not JSON encoded.
     */
    public function read(Config $config)
    {
        $jsonText = \file_get_contents($this->filename);

        if (false === $jsonText) {
            throw new InvalidArgumentException('Cannot read configuration file ' . $this->filename . '.');
        }

        $jsonData = \json_decode($jsonText, true);

        if (false === $jsonData) {
            throw new InvalidArgumentException('Bad json file ' . $this->filename . '.');
        }

        $config->fromArray($this->collapse($jsonData));

        return $this;
    }

    /**
     * Collapses array into a one-dimensional one by imploding nested keys with dash character ('-').
     * @param array $jsonData The json data to flat to one-dimensional array.
     * @return array The collapsed data.
     */
    private function collapse(array $jsonData)
    {
        $iterator = new RecursiveIteratorIterator(new RecursiveArrayIterator($jsonData));
        $result = [];
        foreach ($iterator as $leafValue) {
            $keys = [];
            foreach (\range(0, $iterator->getDepth()) as $depth) {
                $keys[] = $iterator->getSubIterator($depth)->key();
            }
            $result[\implode('-', $keys)] = $leafValue;
        }

        return $result;
    }
}
