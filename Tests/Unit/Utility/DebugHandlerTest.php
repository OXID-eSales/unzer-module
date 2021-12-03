<?php

namespace OxidSolutionCatalysts\Unzer\Tests\Unit\Utility;

use Monolog\Logger;
use OxidSolutionCatalysts\Unzer\Utility\DebugHandler;
use PHPUnit\Framework\TestCase;

class DebugHandlerTest extends TestCase
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
