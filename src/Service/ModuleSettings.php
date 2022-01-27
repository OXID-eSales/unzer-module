<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

namespace OxidSolutionCatalysts\Unzer\Service;

use OxidEsales\EshopCommunity\Internal\Framework\Module\Configuration\Bridge\ModuleSettingBridgeInterface;
use OxidSolutionCatalysts\Unzer\Module;

class ModuleSettings
{
    public const SYSTEM_MODE_SANDBOX = 'sandbox';
    public const SYSTEM_MODE_PRODUCTION = 'production';
    public const PAYMENT_CHARGE = 'charge';
    public const PAYMENT_AUTHORIZE = 'authorize';

    /** @var ModuleSettingBridgeInterface */
    private $moduleSettingBridge;

    /**
     * @param ModuleSettingBridgeInterface $moduleSettingBridge
     */
    public function __construct(
        ModuleSettingBridgeInterface $moduleSettingBridge
    ) {
        $this->moduleSettingBridge = $moduleSettingBridge;
    }

    /**
     * @return bool
     */
    public function isDebugMode(): bool
    {
        return $this->getSettingValue('UnzerDebug') === true;
    }

    /**
     * @return string
     */
    public function getSystemMode(): string
    {
        if ($this->getSettingValue('UnzerSystemMode')) {
            return self::SYSTEM_MODE_PRODUCTION;
        }
        return self::SYSTEM_MODE_SANDBOX;
    }

    /**
     * @return string
     */
    public function getShopPublicKey(): string
    {
        return (string) $this->getSettingValue($this->getSystemMode() . '-UnzerPublicKey');
    }

    /**
     * @return float
     */
    public function getInstallmentRate(): float
    {
        return (float) $this->getSettingValue('UnzerOption_oscunzer_installment_rate');
    }

    /**
     * @return string
     */
    public function getShopPrivateKey(): string
    {
        return (string) $this->getSettingValue($this->getSystemMode() . '-UnzerPrivateKey');
    }

    /**
     * @return string
     */
    public function getAPIKey(): string
    {
        return (string) $this->getSettingValue($this->getSystemMode() . '-UnzerApiKey');
    }

    /**
     * @return string
     */
    public function getRegisteredWebhook(): string
    {
        return (string) $this->getSettingValue('registeredWebhook');
    }

    /**
     * @return bool
     */
    public function useModuleJQueryInFrontend(): bool
    {
        return (bool) $this->getSettingValue('UnzerjQuery');
    }

    /**
     * @param string $paymentMethod
     * @return string
     */
    public function getPaymentProcedureSetting(string $paymentMethod): string
    {
        if (
            $paymentMethod === 'installment-secured' ||
            $this->getSettingValue('UnzerOption_oscunzer_' . $paymentMethod)
        ) {
            return self::PAYMENT_AUTHORIZE;
        }
        return self::PAYMENT_CHARGE;
    }

    /**
     * @return mixed
     */
    private function getSettingValue(string $key)
    {
        return $this->moduleSettingBridge->get($key, Module::MODULE_ID);
    }
}
