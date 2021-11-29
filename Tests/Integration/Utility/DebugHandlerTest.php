<?php

namespace OxidSolutionCatalysts\Unzer\Tests\Integration\Utility;

use OxidEsales\EshopCommunity\Internal\Container\ContainerFactory;
use PHPUnit\Framework\TestCase;
use UnzerSDK\Interfaces\DebugHandlerInterface;

class DebugHandlerTest extends TestCase
{
    public function testLoggerAvailable(): void
    {
        $container = ContainerFactory::getInstance()->getContainer();
        $logger = $container->get('OxidSolutionCatalysts\Unzer\Utility\DebugHandler');

        $this->assertInstanceOf(DebugHandlerInterface::class, $logger);
    }
}