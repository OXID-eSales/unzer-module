<?php

namespace OxidSolutionCatalysts\Unzer\Tests\Unit\Service;

use OxidEsales\EshopCommunity\Internal\Framework\Module\Configuration\Bridge\ModuleSettingBridge;
use OxidEsales\EshopCommunity\Internal\Framework\Module\Configuration\Bridge\ModuleSettingBridgeInterface;
use OxidSolutionCatalysts\Unzer\Module;
use OxidSolutionCatalysts\Unzer\Service\ModuleConfig;
use PHPUnit\Framework\TestCase;

class ModuleConfigTest extends TestCase
{
    /**
     * @dataProvider debugModeDataProvider
     */
    public function testDebugMode($configValue, $expected): void
    {
        $msbMock = $this->createPartialMock(ModuleSettingBridge::class, ['get']);
        $msbMock->method('get')->with('UnzerDebug', Module::MODULE_ID)->willReturn($configValue);
        $sut = $this->getSut($msbMock);

        $this->assertSame($expected, $sut->isDebugMode());
    }

    public function debugModeDataProvider(): array
    {
        return [
            [false, false],
            [true, true],
        ];
    }

    private function getSut(ModuleSettingBridgeInterface $moduleSettingBridge): ModuleConfig
    {
        return new ModuleConfig($moduleSettingBridge);
    }
}