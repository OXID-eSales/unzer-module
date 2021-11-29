<?php

namespace OxidSolutionCatalysts\Unzer\Tests\Integration\Utility;

use Monolog\Logger;
use OxidEsales\EshopCommunity\Internal\Container\ContainerFactory;
use PHPUnit\Framework\TestCase;

class LoggerTest extends TestCase
{
    public function testLoggerAvailable(): void
    {
        $container = ContainerFactory::getInstance()->getContainer();
        $logger = $container->get('OxidSolutionCatalysts\Unzer\Logger');

        $this->assertInstanceOf(Logger::class, $logger);
    }
}