<?php

/*
 * (c) Jean-Fran�ois L�pine <https://twitter.com/Halleck45>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Hal\Application\Config;

/**
 * Extensions configuration
 *
 * @author Jean-François Lépine <https://twitter.com/Halleck45>
 */
class ExtensionsConfiguration
{
    /**
     * @var array
     */
    private $extensions = array();


    /**
     * Constructor
     *
     * @param array $datas
     */
    public function __construct(array $datas = array())
    {
        $this->extensions = $datas;
    }

    /**
     * @return array
     */
    public function getExtensions()
    {
        return $this->extensions;
    }

    /**
     * @param array $extensions
     * @return ExtensionsConfiguration
     */
    public function setExtensions($extensions)
    {
        $this->extensions = $extensions;
        return $this;
    }
}