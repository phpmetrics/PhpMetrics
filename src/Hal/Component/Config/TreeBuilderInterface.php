<?php

/*
 * (c) Jean-François Lépine <https://twitter.com/Halleck45>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Hal\Component\Config;
use Hal\Application\Rule\RuleSet;
use Symfony\Component\Yaml\Yaml;

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
     * @return TreeBuilder
     */
    public function getTree();
}