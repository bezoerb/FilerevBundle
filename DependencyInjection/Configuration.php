<?php

/**
 * Copyright (c) 2015 Ben Zörb
 * Licensed under the MIT license.
 * http://bezoerb.mit-license.org/
 */

namespace Zoerb\Bundle\FilerevBundle\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

/**
 * This is the class that validates and merges configuration from your app/config files
 *
 * To learn more see {@link http://symfony.com/doc/current/cookbook/bundles/extension.html#cookbook-bundles-extension-config-class}
 */
class Configuration implements ConfigurationInterface
{
    /**
     * {@inheritdoc}
     */
    public function getConfigTreeBuilder()
    {
        $treeBuilder = new TreeBuilder();
        $rootNode = $treeBuilder->root('zoerb_filerev');

        $organizeUrls = function ($urls) {
            $urls += array(
                'http' => array(),
                'ssl' => array(),
            );

            foreach ($urls as $i => $url) {
                if (is_int($i)) {
                    if (0 === strpos($url, 'https://') || 0 === strpos($url, '//')) {
                        $urls['http'][] = $urls['ssl'][] = $url;
                    } else {
                        $urls['http'][] = $url;
                    }
                    unset($urls[$i]);
                }
            }

            return $urls;
        };

        $rootNode
            ->info('Filerev configuration')
            ->canBeDisabled()
            ->children()
                ->booleanNode('debug')->defaultValue('%kernel.debug%')->end()
                ->scalarNode('root_dir')->defaultValue('%kernel.root_dir%/../web')->end()
                ->scalarNode('length')->defaultValue(8)->end()
                ->scalarNode('summary_file')->defaultValue('%kernel.root_dir%/config/filerev.json')->end()
                ->arrayNode('assets_base_urls')
                    ->performNoDeepMerging()
                    ->addDefaultsIfNotSet()
                    ->beforeNormalization()
                        ->ifTrue(function ($v) { return !is_array($v); })
                        ->then(function ($v) { return array($v); })
                    ->end()
                    ->beforeNormalization()
                        ->always()
                        ->then($organizeUrls)
                    ->end()
                    ->children()
                        ->arrayNode('http')
                            ->prototype('scalar')->end()
                        ->end()
                        ->arrayNode('ssl')
                            ->prototype('scalar')->end()
                        ->end()
                    ->end()
                ->end()
            ->end();

        return $treeBuilder;
    }
}
