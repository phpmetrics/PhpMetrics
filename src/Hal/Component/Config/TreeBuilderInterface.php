<?php

/*
 * (c) Jean-François Lépine <https://twitter.com/Halleck45>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Hal\Component\Config;

/**
 * Tree of configuration
 *
 * @author Jean-François Lépine <https://twitter.com/Halleck45>
 */
interface TreeBuilderInterface
{

    /**
     * Get the tree used for the application
     *
     * @return \Symfony\Component\Config\Definition\NodeInterface
     */
    public function getTree();
}