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
     * @dataProvider getSettingsDataProvider
     */
    public function testSettings($values, $settingMethod, $settingValue): void
    {
        $sut = new ModuleConfig($this->getBridgeStub($values));
        $this->assertSame($settingValue, $sut->$settingMethod());
    }

    public function getSettingsDataProvider(): array
    {
        return [
            [
                'values' => [
                    ['UnzerDebug', Module::MODULE_ID, true],
                ],
                'settingMethod' => 'isDebugMode',
                'settingValue' => true
            ],
            [
                'values' => [
                    ['UnzerDebug', Module::MODULE_ID, false],
                ],
                'settingMethod' => 'isDebugMode',
                'settingValue' => false
            ],
            [
                'values' => [
                    ['UnzerSystemMode', Module::MODULE_ID, 1],
                ],
                'settingMethod' => 'getSystemMode',
                'settingValue' => ModuleConfig::SYSTEM_MODE_PRODUCTION
            ],
            [
                'values' => [
                    ['UnzerSystemMode', Module::MODULE_ID, 0],
                ],
                'settingMethod' => 'getSystemMode',
                'settingValue' => ModuleConfig::SYSTEM_MODE_SANDBOX
            ],
        ];
    }

    protected function getBridgeStub($valueMap): ModuleSettingBridgeInterface
    {
        $bridgeStub = $this->createPartialMock(
            ModuleSettingBridgeInterface::class,
            ['save', 'get']
        );
        $bridgeStub->method('get')->willReturnMap($valueMap);

        return $bridgeStub;
    }
}