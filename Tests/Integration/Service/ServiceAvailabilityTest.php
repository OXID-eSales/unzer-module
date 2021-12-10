<?php

namespace OxidSolutionCatalysts\Unzer\Tests\Integration\Service;

use OxidEsales\EshopCommunity\Internal\Container\ContainerFactory;
use PHPUnit\Framework\TestCase;

class ServiceAvailabilityTest extends TestCase
{
    /**
     * @dataProvider serviceAvailabilityDataProvider
     */
    public function testLoggerAvailable($service): void
    {
        $container = ContainerFactory::getInstance()->getContainer();
        $this->assertInstanceOf($service, $container->get($service));
    }

    public function serviceAvailabilityDataProvider(): array
    {
        return [
            [\OxidSolutionCatalysts\Unzer\Service\DebugHandler::class],
            [\OxidSolutionCatalysts\Unzer\Service\ModuleSettings::class],
            [\OxidSolutionCatalysts\Unzer\Service\UnzerSDKLoader::class],
            [\OxidSolutionCatalysts\Unzer\Service\Translator::class],
        ];
    }
}
