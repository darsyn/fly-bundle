<?php

namespace Darsyn\Bundle\FlyBundle\Tests\DependencyInjection;

class DefaultConfigurationTest extends AbstractDarsynFlyExtensionTest
{

    public function testDefaultConfiguration()
    {
        $this->loadConfiguration('default');
    }

}
