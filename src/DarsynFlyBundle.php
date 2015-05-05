<?php

namespace Darsyn\Bundle\FlyBundle;

use Darsyn\Bundle\FlyBundle\DependencyInjection\MountManagerCompilerPass;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpKernel\Bundle\Bundle;

/**
 * Bundle Definition
 *
 * @package DarsynFlyBundle
 * @author Zander Baldwin <hello@zanderbaldwin.com>
 */
class DarsynFlyBundle extends Bundle
{
    /**
     * Build
     *
     * @access public
     * @param \Symfony\Component\DependencyInjection\ContainerBuilder $container
     * @return void
     */
    public function build(ContainerBuilder $container)
    {
        parent::build($container);
        $container->addCompilerPass(new MountManagerCompilerPass);
    }
}
