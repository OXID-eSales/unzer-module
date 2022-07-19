<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

namespace OxidSolutionCatalysts\Unzer\Tests\Unit\Service;

use OxidEsales\EshopCommunity\Internal\Framework\Module\Configuration\Bridge\ModuleConfigurationDaoBridgeInterface;
use OxidEsales\EshopCommunity\Internal\Framework\Module\Configuration\Bridge\ModuleSettingBridgeInterface;
use OxidSolutionCatalysts\Unzer\Module;
use OxidSolutionCatalysts\Unzer\Service\ModuleSettings;
use PHPUnit\Framework\TestCase;

class ModuleSettingsTest extends TestCase
{
    /**
     * @dataProvider getSettingsDataProvider
     */
    public function testSettings($values, $settingMethod, $settingValue): void
    {
        $sut = new ModuleSettings(
            $this->getBridgeStub($values),
            $this->getMockBuilder(ModuleConfigurationDaoBridgeInterface::class)
                ->disableOriginalConstructor()
                ->getMock()
        );
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
                    ['UnzerjQuery', Module::MODULE_ID, true],
                ],
                'settingMethod' => 'useModuleJQueryInFrontend',
                'settingValue' => true
            ],
            [
                'values' => [
                    ['UnzerjQuery', Module::MODULE_ID, false],
                ],
                'settingMethod' => 'useModuleJQueryInFrontend',
                'settingValue' => false
            ],
            [
                'values' => [
                    ['UnzerSystemMode', Module::MODULE_ID, 1],
                ],
                'settingMethod' => 'getSystemMode',
                'settingValue' => ModuleSettings::SYSTEM_MODE_PRODUCTION
            ],
            [
                'values' => [
                    ['UnzerSystemMode', Module::MODULE_ID, 0],
                ],
                'settingMethod' => 'getSystemMode',
                'settingValue' => ModuleSettings::SYSTEM_MODE_SANDBOX
            ],
            [
                'values' => [
                    ['UnzerSystemMode', Module::MODULE_ID, 0],
                    ['sandbox-UnzerPublicKey', Module::MODULE_ID, 'sandboxPublicKey'],
                    ['production-UnzerPublicKey', Module::MODULE_ID, 'productionPublicKey'],
                ],
                'settingMethod' => 'getShopPublicKey',
                'settingValue' => 'sandboxPublicKey'
            ],
            [
                'values' => [
                    ['UnzerSystemMode', Module::MODULE_ID, 1],
                    ['sandbox-UnzerPublicKey', Module::MODULE_ID, 'sandboxPublicKey'],
                    ['production-UnzerPublicKey', Module::MODULE_ID, 'productionPublicKey'],
                ],
                'settingMethod' => 'getShopPublicKey',
                'settingValue' => 'productionPublicKey'
            ],
            [
                'values' => [
                    ['UnzerSystemMode', Module::MODULE_ID, 0],
                    ['sandbox-UnzerPrivateKey', Module::MODULE_ID, 'sandboxPrivateKey'],
                    ['production-UnzerPrivateKey', Module::MODULE_ID, 'productionPrivateKey'],
                ],
                'settingMethod' => 'getShopPrivateKey',
                'settingValue' => 'sandboxPrivateKey'
            ],
            [
                'values' => [
                    ['UnzerSystemMode', Module::MODULE_ID, 1],
                    ['sandbox-UnzerPrivateKey', Module::MODULE_ID, 'sandboxPrivateKey'],
                    ['production-UnzerPrivateKey', Module::MODULE_ID, 'productionPrivateKey'],
                ],
                'settingMethod' => 'getShopPrivateKey',
                'settingValue' => 'productionPrivateKey'
            ],
            [
                'values' => [
                    ['registeredWebhook', Module::MODULE_ID, 'Webhook'],
                ],
                'settingMethod' => 'getRegisteredWebhook',
                'settingValue' => "Webhook"
            ],
            [
                'values' => [
                    ['registeredWebhookId', Module::MODULE_ID, 'WebhookId'],
                ],
                'settingMethod' => 'getRegisteredWebhookId',
                'settingValue' => "WebhookId"
            ]
        ];
    }

    private function getBridgeStub($valueMap): ModuleSettingBridgeInterface
    {
        $bridgeStub = $this->getMockBuilder(ModuleSettingBridgeInterface::class)
           ->onlyMethods(['save', 'get'])
           ->getMock();
        $bridgeStub->method('get')->willReturnMap($valueMap);

        return $bridgeStub;
    }
}
