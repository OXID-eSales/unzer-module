<?php

namespace OxidSolutionCatalysts\Unzer\Tests\Unit\Service;

use OxidEsales\Eshop\Core\Config;
use OxidSolutionCatalysts\Unzer\Service\Context;
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

    public function testGetCurrentShopId(): void
    {
        $configStub = $this->createConfiguredMock(Config::class, [
            'getShopId' => 10
        ]);

        $sut = new Context($configStub);
        $this->assertSame(10, $sut->getCurrentShopId());
    }
}
