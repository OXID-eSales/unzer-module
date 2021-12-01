<?php

namespace OxidSolutionCatalysts\Unzer\Tests\Integration\Controller;

use OxidEsales\Eshop\Core\Registry;
use OxidEsales\EshopCommunity\Internal\Container\ContainerFactory;
use OxidEsales\EshopCommunity\Internal\Framework\Module\Configuration\Bridge\ModuleSettingBridgeInterface;
use OxidEsales\TestingLibrary\UnitTestCase;
use OxidSolutionCatalysts\Unzer\Module;
use OxidSolutionCatalysts\Unzer\Service\ModuleSettings;

class ViewConfigTest extends UnitTestCase
{
    public function testModuleSettings()
    {
        $di = ContainerFactory::getInstance()->getContainer();
        $bridge = $di->get(ModuleSettingBridgeInterface::class);
        $bridge->save('UnzerSystemMode', 1, Module::MODULE_ID);
        $bridge->save('production-UnzerPublicKey', 'publickey', Module::MODULE_ID);
        $bridge->save('production-UnzerPrivateKey', 'privatekey', Module::MODULE_ID);
        $bridge->save('production-UnzerApiKey', 'apikey', Module::MODULE_ID);

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

    private function getViewConfig(): \OxidSolutionCatalysts\Unzer\Core\ViewConfig
    {
        return Registry::get(\OxidEsales\Eshop\Core\ViewConfig::class);
    }
}
