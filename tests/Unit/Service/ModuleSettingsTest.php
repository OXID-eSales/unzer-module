<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

declare(strict_types=1);

namespace OxidSolutionCatalysts\Unzer\Tests\Integration\Service;

use OxidEsales\Eshop\Core\Config;
use OxidEsales\Eshop\Core\Session;
use OxidEsales\EshopCommunity\Internal\Container\ContainerFactory;
use OxidEsales\EshopCommunity\Internal\Framework\Module\Facade\ModuleSettingServiceInterface;
use OxidEsales\EshopCommunity\Internal\Transition\Utility\BasicContextInterface;
use OxidSolutionCatalysts\Unzer\Module;
use OxidSolutionCatalysts\Unzer\Service\ModuleSettings;
use OxidSolutionCatalysts\Unzer\Service\DebugHandler;
use OxidEsales\EshopCommunity\Tests\ContainerTrait;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Filesystem\Path;
use Symfony\Component\String\UnicodeString;
use org\bovigo\vfs\vfsStream;
use org\bovigo\vfs\vfsStreamFile;
use Symfony\Component\Filesystem\Filesystem;

class ModuleSettingsTest extends TestCase
{
    use ContainerTrait;

    private ModuleSettingServiceInterface $settingFacade;
    private Session $session;
    private Config $config;
    private DebugHandler $debugHandler;
    private ModuleSettings $moduleSettings;
    private string $testModuleId = 'testUnzerModule';

    protected function setUp(): void
    {
        parent::setUp();
        $this->container = ContainerFactory::getInstance()->getContainer();
        $this->moduleSettings = $this->container->get(ModuleSettings::class);
    }

    public function testSetAndGetSystemMode()
    {
        $this->moduleSettings->saveSetting('UnzerSystemMode', true);
        $this->assertEquals(true, $this->moduleSettings->getSystemMode());
    }

    public function testSetAndGetDebugMode()
    {
        $this->moduleSettings->saveSetting('UnzerDebug', true);
        $isDebugMode = $this->moduleSettings->isDebugMode();
        $this->assertTrue($isDebugMode);
    }

    public function testSetAndGetApplePayNetworks()
    {
        $networks = ['maestro' => '1', 'masterCard' => '1', 'visa' => '1'];
        $this->moduleSettings->saveSetting('applepay_networks', []);
        $result = $this->moduleSettings->getApplePayNetworks();
        $this->assertSame($networks, $result);

        $networks = ['amex' => '1'];
        $this->moduleSettings->saveSetting('applepay_networks', $networks);
        $result = $this->moduleSettings->getApplePayNetworks();
        $this->assertSame(['maestro' => '1', 'masterCard' => '1', 'visa' => '1', 'amex' => '1'], $result);

        $networks = ['maestro' => '1', 'masterCard' => '1', 'visa' => '1'];
        $this->moduleSettings->saveSetting('applepay_networks', []);
        $result = $this->moduleSettings->getApplePayNetworks();
        $this->assertSame($networks, $result);
    }

    public function testSetAndGetModuleVersion()
    {
        $this->assertEquals(Module::MODULE_VERSION, $this->moduleSettings->getModuleVersion());
    }

    public function testSetAndGetGitHubName()
    {
        $this->assertEquals(Module::GITHUB_NAME, $this->moduleSettings->getGitHubName());
    }

    public function testIsStandardEligibilityProduction()
    {
        $this->moduleSettings->saveSetting('production-UnzerPrivateKey', 'private_key');
        $this->moduleSettings->saveSetting('production-UnzerPublicKey', 'public_key');

        $this->assertTrue($this->moduleSettings->isStandardEligibility());
    }

    public function testIsStandardEligibilitySandbox()
    {
        $this->moduleSettings->saveSetting('sandbox-UnzerPrivateKey', 'sandbox_private_key');
        $this->moduleSettings->saveSetting('sandbox-UnzerPublicKey', 'sandbox_public_key');

        $this->assertTrue($this->moduleSettings->isStandardEligibility());
    }

    public function testSetAndGetWebhookConfiguration()
    {
        $webhookConfig = ['event' => 'payment_success'];
        $this->moduleSettings->saveSetting('webhookConfiguration', $webhookConfig);

        $this->assertEquals($webhookConfig, $this->moduleSettings->getWebhookConfiguration());
    }
}
