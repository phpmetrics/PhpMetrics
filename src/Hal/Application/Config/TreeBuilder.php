<?php

/*
 * (c) Jean-François Lépine <https://twitter.com/Halleck45>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Hal\Application\Config;

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
     * @return \Symfony\Component\Config\Definition\NodeInterface
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
                        ->scalarNode('directory')->defaultValue(null)->end()
                        ->scalarNode('exclude')->defaultValue('Tests|tests|Features|features|\.svn|\.git|vendor')->end()
                        ->scalarNode('extensions')->defaultValue('php|inc')->end()
                        ->booleanNode('symlinks')->defaultValue(false)->end()
                    ->end()
                ->end()
                ->arrayNode('logging')
                    ->addDefaultsIfNotSet()
                    ->children()
                        ->arrayNode('report')
                            ->children()
                                ->scalarNode('html')->defaultValue(null)->end()
                                ->scalarNode('xml')->defaultValue(null)->end()
                                ->scalarNode('csv')->defaultValue(null)->end()
                                ->scalarNode('json')->defaultValue(null)->end()
                                ->scalarNode('cli')->defaultValue(null)->end()
                            ->end()
                        ->end()
                        ->arrayNode('violations')
                            ->children()
                                ->scalarNode('xml')->defaultValue(null)->end()
                            ->end()
                        ->end()
                        ->arrayNode('chart')
                            ->children()
                                ->scalarNode('bubbles')->defaultValue(null)->end()
                            ->end()
                        ->end()
                    ->end()
                ->end()
            ->end()
        ;

        return $treeBuilder->buildTree();
    }
}