<?php

namespace OxidSolutionCatalysts\Unzer\Tests\Integration\Service;

use OxidEsales\EshopCommunity\Internal\Container\ContainerFactory;
use OxidSolutionCatalysts\Unzer\Service\ModuleSettings;
use PHPUnit\Framework\TestCase;

class ModuleSettingsTest extends TestCase
{
    public function testServiceAvailable(): void
    {
        $container = ContainerFactory::getInstance()->getContainer();
        $service = $container->get(ModuleSettings::class);

        $this->assertInstanceOf(ModuleSettings::class, $service);
    }
}
