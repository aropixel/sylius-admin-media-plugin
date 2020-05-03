<?php

namespace Aropixel\SyliusAdminMediaPlugin\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

class Configuration implements ConfigurationInterface
{

    public function getConfigTreeBuilder()
    {
        $treeBuilder = new TreeBuilder('aropixel_sylius_admin_media');

        $treeBuilder->getRootNode()
                    ->children()

                        ->arrayNode('entities_crops')
                            ->prototype('variable')->end()
                            ->children()
                                ->arrayNode('types')
                                    ->prototype('variable')->end()
                                    ->children()
                                        ->scalarNode('crop')->end()
                                    ->end()
                                ->end()
                            ->end()
                        ->end()

                        ->arrayNode('default_crops')
                            ->prototype('variable')->end()
                            ->children()
                                ->arrayNode('types')
                                    ->prototype('variable')->end()
                                    ->children()
                                        ->scalarNode('crop')->end()
                                    ->end()
                                ->end()
                            ->end()
                        ->end()

                    ->end()
        ;


        return $treeBuilder;
    }
}
