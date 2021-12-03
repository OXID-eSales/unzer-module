<?php

namespace OxidSolutionCatalysts\Unzer\Tests\Integration\Service;

use OxidEsales\EshopCommunity\Internal\Container\ContainerFactory;
use OxidSolutionCatalysts\Unzer\Service\UnzerSDKLoader;
use PHPUnit\Framework\TestCase;

class UnzerSDKLoaderTest extends TestCase
{
    public function testServiceAvailable(): void
    {
        $container = ContainerFactory::getInstance()->getContainer();
        $service = $container->get(UnzerSDKLoader::class);

        $this->assertInstanceOf(UnzerSDKLoader::class, $service);
    }
}
