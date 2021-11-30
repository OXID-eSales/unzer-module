<?php

namespace OxidSolutionCatalysts\Unzer\Tests\Integration\Service;

use OxidEsales\EshopCommunity\Internal\Container\ContainerFactory;
use OxidSolutionCatalysts\Unzer\Service\ModuleConfig;
use PHPUnit\Framework\TestCase;

class ModuleConfigTest extends TestCase
{
    public function testServiceAvailable(): void
    {
        $container = ContainerFactory::getInstance()->getContainer();
        $service = $container->get(ModuleConfig::class);

        $this->assertIsObject($service);
    }
}