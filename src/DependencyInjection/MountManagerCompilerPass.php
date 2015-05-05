<?php
/**
 * Created by PhpStorm.
 * User: zander
 * Date: 05/05/15
 * Time: 16:50
 */

namespace Darsyn\Bundle\FlyBundle\DependencyInjection;

use Darsyn\Bundle\FlyBundle\DarsynFlyBundle;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\Reference;

/**
 * MountManager Compiler Class
 *
 * @package DarsynFlyBundle
 * @author Zander Baldwin <hello@zanderbaldwin.com>
 */
class MountManagerCompilerPass implements CompilerPassInterface
{
    const CONTAINER_TAG = 'darsyn_fly.adapter';

    /**
     * Process
     *
     * @access public
     * @param \Symfony\Component\DependencyInjection\ContainerBuilder $container
     * @return void
     */
    public function process(ContainerBuilder $container)
    {
        if (!$container->has(DarsynFlyBundle::SERVICE_NAME)) {
            return null;
        }
        $mountManager = $container->findDefinition(DarsynFlyBundle::SERVICE_NAME);
        $taggedServices = $container->findTaggedServiceIds(DarsynFlyBundle::TAG_NAME);

        foreach ($taggedServices as $id => $tags) {
            $definition->addMethodCall('mountFilesystem', [
                new Reference($id),
                $tags['protocol']
            ]);
        }
    }
}
