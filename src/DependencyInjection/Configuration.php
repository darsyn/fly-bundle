<?php

namespace Darsyn\Bundle\FlyBundle\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

/**
 * This is the class that validates and merges configuration from your app/config files. To learn more see
 * http://symfony.com/doc/current/cookbook/bundles/extension.html#cookbook-bundles-extension-config-class
 */
class Configuration implements ConfigurationInterface
{
    /**
     * {@inheritdoc}
     */
    public function getConfigTreeBuilder()
    {
        $treeBuilder = new TreeBuilder();
        $rootNode = $treeBuilder->root('darsyn_fly');

        $rootNode->addDefaultsIfNotSet()
            ->children()
                ->scalarNode('alias')->defaultValue('flysystem')->end()
                ->scalarNode('project_adapter')->defaultValue('project')->end()
                ->scalarNode('cache')->defaultValue(null)->end()
            ->end();

        // Here you should define the parameters that are allowed to
        // configure your bundle. See the documentation linked above for
        // more information on that topic.

        return $treeBuilder;
    }
}
