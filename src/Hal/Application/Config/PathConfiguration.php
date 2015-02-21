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
     * @var bool
     */
    private $followSymlinks = false;

    /**
     * @param mixed $basePath
     * @return self
     */
    public function setBasePath($basePath)
    {
        $this->basePath = $basePath;
        return $this;
    }

    /**
     * @return string
     */
    public function getBasePath()
    {
        return $this->basePath;
    }

    /**
     * @param mixed $excludedDirs
     * @return self
     */
    public function setExcludedDirs($excludedDirs)
    {
        $this->excludedDirs = $excludedDirs;
        return $this;
    }

    /**
     * @return string
     */
    public function getExcludedDirs()
    {
        return $this->excludedDirs;
    }

    /**
     * @param mixed $extensions
     * @return self
     */
    public function setExtensions($extensions)
    {
        $this->extensions = $extensions;
        return $this;
    }

    /**
     * @return string
     */
    public function getExtensions()
    {
        return $this->extensions;
    }

    /**
     * @return boolean
     */
    public function isFollowSymlinks()
    {
        return $this->followSymlinks;
    }

    /**
     * @param boolean $followSymlinks
     * @return self
     */
    public function setFollowSymlinks($followSymlinks)
    {
        $this->followSymlinks = (bool) $followSymlinks;
        return $this;
    }



}