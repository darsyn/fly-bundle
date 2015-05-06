<?php

namespace Darsyn\Bundle\FlyBundle\DependencyInjection;

use Darsyn\Bundle\FlyBundle\DarsynFlyBundle;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\Exception\InvalidArgumentException;
use Symfony\Component\DependencyInjection\Exception\ServiceNotFoundException;
use Symfony\Component\DependencyInjection\Reference;

/**
 * MountManager Compiler Class
 *
 * @package DarsynFlyBundle
 * @author Zander Baldwin <hello@zanderbaldwin.com>
 */
class MountManagerCompilerPass implements CompilerPassInterface
{
    /**
     * Process the service container.
     * We don't have to worry about speed or performance (as much as we would in a normal request) as this gets compiled
     * to cache ahead-of-time.
     *
     * @access public
     * @param \Symfony\Component\DependencyInjection\ContainerBuilder $container
     * @return void
     */
    public function process(ContainerBuilder $container)
    {
        if (!$container->has(DarsynFlyBundle::SERVICE_NAME)) {
            throw new ServiceNotFoundException(DarsynFlyBundle::SERVICE_NAME);
        }
        $mountManagerDefinition = $container->findDefinition(DarsynFlyBundle::SERVICE_NAME);

        $cacheServiceReference = null;
        if ($container->hasParameter('darsyn_fly.cache_service')) {
            $cacheServiceReference = new Reference($container->getParameter('darsyn_fly.cache_service'));
        }

        // Iterate through each service we found tagged with the adapter tag.
        foreach ($container->findTaggedServiceIds(DarsynFlyBundle::ADAPTER_TAG) as $id => $tags) {
            foreach ($tags as $attr) {
                // Make sure that a valid scheme has been defined.
                if (!isset($attr['scheme']) || !preg_match('/^[a-z][a-z\\d\\.\\+-]*$/i', $attr['scheme'])) {
                    throw new InvalidArgumentException(sprintf(
                        'Invalid scheme set for tagged Flysystem adapter service "%s".',
                        $id
                    ));
                }
                // Check if we should wrap this in a cache service.
                $adapterDefinition = new Reference($id);
                if (!empty($cacheServiceReference) || isset($attr['cache'])) {
                    $adapterCacheServiceDefinition = isset($attr['cache'])
                        ? new Reference($attr['cache'])
                        : $cacheServiceReference;
                    $adapterDefinition = new Definition('League\\Flysystem\\Cached\\CachedAdapter', [
                        new Reference($id),
                        $adapterCacheServiceDefinition
                    ]);
                }
                // Add the adapter to the mount manager service definition.
                $mountManagerDefinition->addMethodCall('mountFilesystem', [
                    $attr['scheme'],
                    new Definition('League\\Flysystem\\Filesystem', [
                        $adapterDefinition,
                    ]),
                ]);
            }
        }

        // Iterate through each service we found tagged with the plugin tag.
        foreach ($container->findTaggedServiceIds(DarsynFlyBundle::PLUGIN_TAG) as $id => $tags) {
            foreach ($tags as $attr) {
                $mountManagerDefinition->addMethodCall('addPlugin', [new Reference($id)]);
            }
        }
    }
}
