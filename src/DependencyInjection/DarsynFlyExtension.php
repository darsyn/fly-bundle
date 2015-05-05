<?php

namespace Darsyn\Bundle\FlyBundle\DependencyInjection;

use Darsyn\Bundle\FlyBundle\DarsynFlyBundle;
use League\Flysystem\Adapter\Local;
use League\Flysystem\Filesystem;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\HttpKernel\DependencyInjection\Extension;
use Symfony\Component\DependencyInjection\Loader;

/**
 * This is the class that loads and manages your bundle configuration. To learn more see
 * http://symfony.com/doc/current/cookbook/bundles/extension.html
 */
class DarsynFlyExtension extends Extension
{
    /**
     * {@inheritdoc}
     */
    public function load(array $configs, ContainerBuilder $container)
    {
        $configuration = new Configuration();
        $config = $this->processConfiguration($configuration, $configs);

        $projectAdapter = new Definition('League\\Flysystem\\Adapter\\Local', [
            $container->getParameter('kernel.root_dir') . '/../'
        ]);
        $projectAdapter->addTag(DarsynFlyBundle::TAG_NAME, ['protocol' => 'project']);

        $mountManager = new Definition('League\\Flysystem\\MountManager');
        $container->addDefinitions([
            DarsynFlyBundle::SERVICE_NAME => $mountManager,
            'darsyn_fly.adapter.project' => $projectAdapter,
        ]);

        if (isset($config['alias']) && !empty($config['alias'])) {
            $container->addAliases([
                $config['alias'] => 'darsyn_fly',
            ]);
        }
    }
}
