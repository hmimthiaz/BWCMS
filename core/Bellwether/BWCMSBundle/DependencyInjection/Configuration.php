<?php

namespace Bellwether\BWCMSBundle\DependencyInjection;

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
        $rootNode = $treeBuilder->root('bwcms');

        $rootNode->children()
            ->arrayNode('media')
                ->addDefaultsIfNotSet()
                ->children()
                    ->scalarNode('path')->defaultValue('media')->end()
                    ->integerNode('maxUploadSize')->defaultValue(1000)->end()
                    ->scalarNode('blockedExtension')->defaultValue('dll,exe,sh,php')->end()
                    ->booleanNode('s3Enabled')->defaultValue(false)->end()
                    ->scalarNode('s3Bucket')->defaultValue(null)->end()
                    ->scalarNode('s3Domain')->defaultValue('s3.amazonaws.com')->end()
                ->end()
            ->end()
        ->end();

        return $treeBuilder;
    }
}
