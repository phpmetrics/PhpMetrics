<?php
/**
 * (c) Jean-François Lépine <https://twitter.com/Halleck45>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Hal\Application\Config\File;

/**
 * Trait TraitFileName
 *
 * Contains methods and properties that helps to define a configuration filename.
 *
 * @package Hal\Application\Config\File
 */
trait TraitFileName
{
    /** @var string Name of the configuration file. */
    private $filename;

    /**
     * Constructor.
     * Set the filename given from argument in the associated property.
     * @param string $filename Name of the configuration file.
     */
    public function __construct($filename)
    {
        $this->filename = $filename;
    }
}
