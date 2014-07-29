<?php

/*
 * (c) Jean-François Lépine <https://twitter.com/Halleck45>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Hal\Application\Config;

/**
 * Path configuration
 *
 * @author Jean-François Lépine <https://twitter.com/Halleck45>
 */
class PathConfiguration
{
    /**
     * @var string
     */
    private $excludedDirs;

    /**
     * @var string
     */
    private $extensions;

    /**
     * @var string
     */
    private $basePath;

    /**
     * @param mixed $basePath
     * @return $this;
     */
    public function setBasePath($basePath)
    {
        $this->basePath = $basePath;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getBasePath()
    {
        return $this->basePath;
    }

    /**
     * @param mixed $excludedDirs
     * @return $this;
     */
    public function setExcludedDirs($excludedDirs)
    {
        $this->excludedDirs = $excludedDirs;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getExcludedDirs()
    {
        return $this->excludedDirs;
    }

    /**
     * @param mixed $extensions
     * @return $this;
     */
    public function setExtensions($extensions)
    {
        $this->extensions = $extensions;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getExtensions()
    {
        return $this->extensions;
    }

}