<?php

namespace OxidSolutionCatalysts\Unzer\Tests\Unit\Utility;

use OxidEsales\Eshop\Core\Config;
use OxidSolutionCatalysts\Unzer\Utility\Context;
use PHPUnit\Framework\TestCase;

class ContextTest extends TestCase
{
    /**
     * @dataProvider logPathDataProvider
     */
    public function testGetPaymentLogFilePath($configValue): void
    {
        $configStub = $this->createConfiguredMock(Config::class, [
            'getLogsDir' => $configValue
        ]);

        $sut = new Context($configStub);
        $this->assertSame(
            "logsDir/unzer/unzer_" . date("Y-m-d") . ".log",
            $sut->getUnzerLogFilePath()
        );
    }

    public function logPathDataProvider(): array
    {
        return [
            ['logsDir/'],
            ['logsDir']
        ];
    }
}