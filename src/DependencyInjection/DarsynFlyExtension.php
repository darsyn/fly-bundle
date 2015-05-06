<?php

namespace Darsyn\Bundle\FlyBundle\DependencyInjection;

use Darsyn\Bundle\FlyBundle\DarsynFlyBundle;
use League\Flysystem\Adapter\Local;
use League\Flysystem\Filesystem;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\Exception\ServiceNotFoundException;
use Symfony\Component\HttpKernel\DependencyInjection\Extension;
use Symfony\Component\DependencyInjection\Loader;

/**
 * This is the class that loads and manages your bundle configuration. To learn more see
 * http://symfony.com/doc/current/cookbook/bundles/extension.html
 */
class DarsynFlyExtension extends Extension
{
    /**
     * Load Configuration
     *
     * @access public
     * @param array $configs
     * @param \Symfony\Component\DependencyInjection\ContainerBuilder $container
     * @return void
     */
    public function load(array $configs, ContainerBuilder $container)
    {
        $configuration = new Configuration();
        $config = $this->processConfiguration($configuration, $configs);

        // Set the main service definition.
        $container->setDefinition(
            DarsynFlyBundle::SERVICE_NAME,
            $mountManager = new Definition('League\\Flysystem\\MountManager')
        );

        // Cache service configuration.
        if (!empty($config['cache'])) {
            if (!$container->has($config['cache'])) {
                throw new ServiceNotFoundException($config['cache']);
            }
            $container->setParameter('darsyn_fly.cache_service', $config['cache']);
        }

        // If the configuration allows it, create a primary Local adapter at the root directory of the project.
        if (is_string($config['project_adapter']) && !empty($config['project_adapter'])) {
            $projectAdapter = new Definition('League\\Flysystem\\Adapter\\Local', [
                $container->getParameter('kernel.root_dir') . '/../'
            ]);
            $projectAdapter->addTag(DarsynFlyBundle::ADAPTER_TAG, ['scheme' => $config['project_adapter']]);
            $container->setDefinition(
                sprintf('%s.adapter.%s', DarsynFlyBundle::SERVICE_NAME, $config['project_adapter']),
                $projectAdapter
            );
        }

        // If the configuration has set an alias ("flysystem" by default), set it here.
        if (isset($config['alias']) && is_string($config['alias']) && !empty($config['alias'])) {
            $container->setAlias($config['alias'], DarsynFlyBundle::SERVICE_NAME);
        }
    }
}
