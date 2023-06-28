<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

namespace OxidSolutionCatalysts\Unzer\Tests\Unit\Service;

use Monolog\Logger;
use OxidEsales\EshopCommunity\Tests\Integration\IntegrationTestCase;
use OxidSolutionCatalysts\Unzer\Service\DebugHandler;

class DebugHandlerTest extends IntegrationTestCase
{
    public function testLoggerAvailable(): void
    {
        $testMessage = 'someMessage';

        $loggerMock = $this->createPartialMock(Logger::class, ['info']);
        $loggerMock->expects($this->once())->method('info')->with($testMessage);

        $sut = new DebugHandler($loggerMock);
        $sut->log($testMessage);
    }
}
