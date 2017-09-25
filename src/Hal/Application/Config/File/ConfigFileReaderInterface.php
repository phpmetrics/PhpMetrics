<?php
/**
 * (c) Jean-François Lépine <https://twitter.com/Halleck45>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Hal\Application\Config\File;

use Hal\Application\Config\Config;

/**
 * Interface ConfigFileReaderInterface
 *
 * Contract about all configuration file readers that must read a config file.
 *
 * @package Hal\Application\Config\File
 */
interface ConfigFileReaderInterface
{
    /**
     * Read a config
     * @param Config $config
     * @return $this
     */
    public function read(Config $config);
}
