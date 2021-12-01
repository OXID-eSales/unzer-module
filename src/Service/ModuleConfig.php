<?php

namespace OxidSolutionCatalysts\Unzer\Service;

use OxidEsales\EshopCommunity\Internal\Framework\Module\Configuration\Bridge\ModuleSettingBridgeInterface;
use OxidSolutionCatalysts\Unzer\Module;

class ModuleConfig
{
    public const SYSTEM_MODE_SANDBOX = 'sandbox';
    public const SYSTEM_MODE_PRODUCTION = 'production';

    /** @var ModuleSettingBridgeInterface */
    private $moduleSettingBridge;

    public function __construct(
        ModuleSettingBridgeInterface $moduleSettingBridge
    ) {
        $this->moduleSettingBridge = $moduleSettingBridge;
    }

    public function isDebugMode(): bool
    {
        return $this->getSettingValue('UnzerDebug') === true;
    }

    public function getSystemMode(): string
    {
        if ($this->getSettingValue('UnzerSystemMode')) {
            return "production";
        }
        return "sandbox";
    }

    public function getShopPublicKey(): string
    {
        return $this->getSettingValue($this->getSystemMode() . '-UnzerPublicKey');
    }

    public function getShopPrivateKey(): string
    {
        return $this->getSettingValue($this->getSystemMode() . '-UnzerPrivateKey');
    }

    public function getAPIKey(): string
    {
        return $this->getSettingValue($this->getSystemMode() . '-UnzerApiKey');
    }

    protected function getSettingValue($key)
    {
        return $this->moduleSettingBridge->get($key, Module::MODULE_ID);
    }
}