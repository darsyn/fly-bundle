<?php

namespace Darsyn\Bundle\FlyBundle\Tests\DependencyInjection;

use Darsyn\Bundle\FlyBundle\DependencyInjection\DarsynFlyExtension;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\DependencyInjection\Loader;

/**
 * @author Zander Baldwin <hello@zanderbaldwin.com>
 */
abstract class AbstractDarsynFlyExtensionTest extends \PHPUnit_Framework_TestCase implements ContainerAwareInterface
{
    /**
     * @access private
     * @var \Darsyn\Bundle\FlyBundle\DependencyInjection\DarsynFlyExtension
     */
    private $extension;

    /**
     * @access private
     * @var \Symfony\Component\DependencyInjection\ContainerBuilder
     */
    private $container;

    /**
     * Get Service Container
     *
     * @access public
     * @return \Symfony\Component\DependencyInjection\ContainerInterface
     */
    public function getContainer()
    {
        if ($this->container === null) {
            $this->setContainer(new ContainerBuilder);
        }
        return $this->container;
    }

    /**
     * Set Service Container
     *
     * @access public
     * @param \Symfony\Component\DependencyInjection\ContainerInterface $container
     * @return void
     */
    public function setContainer(ContainerInterface $container = null)
    {
        $this->container = $container;
    }

    /**
     * Setup
     *
     * @access protected
     * @return void
     */
    protected function setUp()
    {
        $this->extension = new DarsynFlyExtension;
        $this->container = new ContainerBuilder;
        $this->container->registerExtension($this->extension);
    }

    /**
     * Load Configuration
     *
     * @access public
     * @param string $resource
     * @return void
     */
    final protected function loadConfiguration($resource)
    {
        $loader = new Loader\YamlFileLoader($this->getContainer(), new FileLocator(__DIR__ . '/Fixtures/Config/'));
        $loader->load($resource . '.yml');
    }

    /**
     * Test Without Configuration
     *
     * @access public
     * @return void
     */
    public function testWithoutConfiguration()
    {
        // An extension is only loaded in the container if a configuration is provided for it.
        // Then, we need to explicitly load it.
        $this->container->loadFromExtension($this->extension->getAlias());
        $this->container->compile();

        $this->assertFalse($this->container->has('darsyn_fly'));
        $this->assertFalse($this->container->has('flysystem'));
    }
}
