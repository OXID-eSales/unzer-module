<?php

namespace OxidSolutionCatalysts\Unzer\Tests\Integration\Service;

use OxidEsales\EshopCommunity\Internal\Container\ContainerFactory;
use PHPUnit\Framework\TestCase;
use UnzerSDK\Interfaces\DebugHandlerInterface;

class DebugHandlerTest extends TestCase
{
    public function testLoggerAvailable(): void
    {
        $container = ContainerFactory::getInstance()->getContainer();
        $logger = $container->get('OxidSolutionCatalysts\Unzer\Service\DebugHandler');

        $this->assertInstanceOf(DebugHandlerInterface::class, $logger);
    }
}
