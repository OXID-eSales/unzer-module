<?php

namespace OxidSolutionCatalysts\Unzer\Service;

use OxidEsales\EshopCommunity\Internal\Framework\Module\Configuration\Bridge\ModuleSettingBridgeInterface;
use OxidSolutionCatalysts\Unzer\Module;

class ModuleSettings
{
    public const SYSTEM_MODE_SANDBOX = 'sandbox';
    public const SYSTEM_MODE_PRODUCTION = 'production';
    public const PAYMENT_DIRECT = 'direct Capture';
    public const PAYMENT_AUTHORIZE = 'Authorize & later Capture';

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
            return self::SYSTEM_MODE_PRODUCTION;
        }
        return self::SYSTEM_MODE_SANDBOX;
    }

    public function getShopPublicKey(): string
    {
        return (string) $this->getSettingValue($this->getSystemMode() . '-UnzerPublicKey');
    }

    public function getShopPrivateKey(): string
    {
        return (string) $this->getSettingValue($this->getSystemMode() . '-UnzerPrivateKey');
    }

    public function getAPIKey(): string
    {
        return (string) $this->getSettingValue($this->getSystemMode() . '-UnzerApiKey');
    }

    public function getPaymentProcedureSetting($id): string
    {
        if ($this->getSettingValue('UnzerOption_' . $id)) {
            return self::PAYMENT_AUTHORIZE;
        }
        return self::PAYMENT_DIRECT;
    }

    /**
     * @return mixed
     */
    private function getSettingValue(string $key)
    {
        return $this->moduleSettingBridge->get($key, Module::MODULE_ID);
    }
}
