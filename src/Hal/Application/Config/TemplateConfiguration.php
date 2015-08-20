<?php

/*
 * (c) Jean-François Lépine <https://twitter.com/Halleck45>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Hal\Application\Config;

/**
 * Template configuration
 *
 * @author Ramunas Geciauskas <ramunas@geciauskas.com>
 */
class TemplateConfiguration
{
    /** @var string Template title name */
    private $title;

    /**
     * @param array $defaults Default settings
     */
    public function __construct(array $defaults = array())
    {
        $this->title = empty($defaults['title']) ? 'PhpMetrics report' : $defaults['title'];
    }

    /**
     * @param string $title
     * @return self
     */
    public function setTitle($title)
    {
        $this->title = $title;
        return $this;
    }

    /**
     * @return string
     */
    public function getTitle()
    {
        return $this->title;
    }
}