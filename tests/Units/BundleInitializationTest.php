<?php

namespace MeteoConcept\ReactMqttBundle\Tests\Units;

use Nyholm\BundleTest\BaseBundleTestCase;

use MeteoConcept\ReactMqttBundle\MeteoConceptReactMqttBundle;

class BundleInitializationTest extends BaseBundleTestCase
{
    protected function getBundleClass()
    {
        return MeteoConceptReactMqttBundle::class;
    }

    public function setUp(): void
    {
        $kernel = $this->createKernel();
        $this->bootKernel();
    }

    public function test_the_container_is_buildable()
    {
        $container = $this->getContainer();

        $this->assertNotNull($container);
    }
}