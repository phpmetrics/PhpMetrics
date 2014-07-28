<?php

/*
 * (c) Jean-François Lépine <https://twitter.com/Halleck45>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Hal\Application\Config;
use Symfony\Component\Config\Definition\Builder\NodeInterface;

/**
 * Builds Tree of configuration
 *
 * @author Jean-François Lépine <https://twitter.com/Halleck45>
 */
class TreeBuilder implements \Hal\Component\Config\TreeBuilderInterface
{

    /**
     * Get the tree used for the application
     *
     * @return NodeInterface
     */
    public function getTree() {
        $treeBuilder = new \Symfony\Component\Config\Definition\Builder\TreeBuilder();
        $rootNode = $treeBuilder->root('phpmetrics');

        $rootNode
            ->children()
                ->arrayNode('rules')
                    ->useAttributeAsKey('rulename')
                    ->prototype('array')
                        ->children()
                            ->scalarNode('0')->end()
                            ->scalarNode('1')->end()
                            ->scalarNode('2')->end()
                        ->end()
                    ->end()
                ->end()
                ->scalarNode('failure')
                    ->defaultValue(null)
                ->end()
                ->arrayNode('path')
                    ->addDefaultsIfNotSet()
                    ->children()
                        ->scalarNode('exclude')->defaultValue('Tests|tests|Features|features')->end()
                        ->scalarNode('extensions')->defaultValue('php|inc')->end()
                    ->end()
                ->end()
            ->end()
        ;

        return $treeBuilder->buildTree();
    }
}