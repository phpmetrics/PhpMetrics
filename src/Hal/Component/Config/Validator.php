<?php

/*
 * (c) Jean-François Lépine <https://twitter.com/Halleck45>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Hal\Component\Config;
use Symfony\Component\Config\Definition\Processor;
use \Symfony\Component\Config\Definition\NodeInterface;

/**
 * Valides a config file
 *
 * @author Jean-François Lépine <https://twitter.com/Halleck45>
 */
class Validator
{
    /**
     * @var NodeInterface
     */
    private $treeBuilder;

    /**
     * Constructor
     *
     * @param NodeInterface $tree
     */
    public function __construct(NodeInterface $tree) {
        $this->tree = $tree;
    }

    /**
     * Validates the config file according tree
     *
     * @param array $config
     * @return array
     */
    public function validates(array $config) {
        $processor = new Processor();
        return $processor->process($this->tree, $config);
    }
}