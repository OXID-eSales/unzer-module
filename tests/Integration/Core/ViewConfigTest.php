<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

namespace OxidSolutionCatalysts\Unzer\Tests\Integration\Core;

use OxidEsales\Eshop\Core\Registry;
use OxidEsales\EshopCommunity\Internal\Container\ContainerFactory;
use OxidEsales\EshopCommunity\Internal\Framework\Module\Configuration\Bridge\ModuleSettingBridgeInterface;
use OxidEsales\EshopCommunity\Tests\Integration\IntegrationTestCase;
use OxidSolutionCatalysts\Unzer\Module;
use OxidSolutionCatalysts\Unzer\Service\ModuleSettings;
use OxidEsales\Eshop\Core\ViewConfig;

class ViewConfigTest extends IntegrationTestCase
{
    private ModuleSettings $moduleSettings;

    public function setUp(): void
    {
        parent::setUp();
        $this->container = ContainerFactory::getInstance()->getContainer();
        $this->moduleSettings = $this->container->get(ModuleSettings::class);
    }

    public function testModuleSettings()
    {
        $this->moduleSettings->saveSetting('UnzerSystemMode', true);
        $this->moduleSettings->saveSetting('production-UnzerPublicKey', 'publickey');
        $this->moduleSettings->saveSetting('production-UnzerPrivateKey', 'privatekey');

        $viewConfig = $this->getViewConfig();
        $this->assertSame(ModuleSettings::SYSTEM_MODE_PRODUCTION, $viewConfig->getUnzerSystemMode());
        $this->assertSame('publickey', $viewConfig->getUnzerPubKey());
        $this->assertSame('privatekey', $viewConfig->getUnzerPrivKey());
    }

    public function testGetSessionPaymentInfo()
    {
        $testValue = 'something';
        $session = Registry::getSession();
        $session->setVariable('additionalPaymentInformation', $testValue);

        $viewConfig = $this->getViewConfig();
        $this->assertSame($testValue, $viewConfig->getSessionPaymentInfo());
    }

    private function getViewConfig(): ViewConfig
    {
        return Registry::get(ViewConfig::class);
    }
}
