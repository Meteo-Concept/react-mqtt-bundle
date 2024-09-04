<?php

namespace MeteoConcept\ReactMqttBundle\Tests\Units;

use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\HttpKernel\KernelInterface;
use Nyholm\BundleTest\TestKernel;

use MeteoConcept\ReactMqttBundle\MeteoConceptReactMqttBundle;

class BundleInitializationTest extends KernelTestCase
{
    protected static function getKernelClass(): string
    {
        return TestKernel::class;
    }

    protected static function createKernel(array $options = []): KernelInterface
    {
        /**
         * @var TestKernel $kernel
         */
        $kernel = parent::createKernel($options);
        $kernel->addTestBundle(MeteoConceptReactMqttBundle::class);
        $kernel->handleOptions($options);

        return $kernel;
    }

    public function test_the_container_is_buildable()
    {
        // Boot the kernel.
        $kernel = self::bootKernel();

        // Get the container
        $container = $kernel->getContainer();

        $this->assertNotNull($container);
    }
}
