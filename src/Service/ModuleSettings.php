<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

namespace OxidSolutionCatalysts\Unzer\Service;

use http\Exception\RuntimeException;
use OxidEsales\Eshop\Core\Registry;
use OxidEsales\EshopCommunity\Core\Exception\FileException;
use OxidEsales\EshopCommunity\Core\ViewConfig;
use OxidEsales\EshopCommunity\Internal\Framework\Module\Configuration\Bridge\ModuleSettingBridgeInterface;
use OxidSolutionCatalysts\Unzer\Module;

class ModuleSettings
{

    public const SYSTEM_MODE_SANDBOX = 'sandbox';
    public const SYSTEM_MODE_PRODUCTION = 'production';
    public const PAYMENT_CHARGE = 'charge';
    public const PAYMENT_AUTHORIZE = 'authorize';

    public const APPLE_PAY_MERCHANT_CAPABILITIES = [
        'supportsCredit' => '0',
        'supportsDebit' => '0',
        'supportsEMV' => '0'
    ];

    public const APPLE_PAY_NETWORKS = [
        'amex' => '0',
        'cartesBancaires' => '0',
        'chinaUnionPay' => '0',
        'discover' => '0',
        'eftpos' => '0',
        'electron' => '0',
        'elo' => '0',
        'interac' => '0',
        'jcb' => '0',
        'mada' => '0',
        'maestro' => '0',
        'masterCard' => '0',
        'privateLabel' => '0',
        'visa' => '0',
        'vPay' => '0'
    ];

    /** @var ModuleSettingBridgeInterface */
    private $moduleSettingBridge;

    /**
     * @param ModuleSettingBridgeInterface $moduleSettingBridge
     */
    public function __construct(
        ModuleSettingBridgeInterface $moduleSettingBridge
    )
    {
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
        return (string)$this->getSettingValue($this->getSystemMode() . '-UnzerPublicKey');
    }

    /**
     * @return float
     */
    public function getInstallmentRate(): float
    {
        return (float)$this->getSettingValue('UnzerOption_oscunzer_installment_rate');
    }

    /**
     * @return string
     */
    public function getShopPrivateKey(): string
    {
        return (string)$this->getSettingValue($this->getSystemMode() . '-UnzerPrivateKey');
    }

    /**
     * @return string
     */
    public function getAPIKey(): string
    {
        return (string)$this->getSettingValue($this->getSystemMode() . '-UnzerApiKey');
    }

    /**
     * @return string
     */
    public function getRegisteredWebhook(): string
    {
        return (string)$this->getSettingValue('registeredWebhook');
    }

    /**
     * @return bool
     */
    public function useModuleJQueryInFrontend(): bool
    {
        return (bool)$this->getSettingValue('UnzerjQuery');
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
    public function getApplePayLabel()
    {
        return $this->getSettingValue('applepay_label') ?: Registry::getConfig()->getActiveShop()->oxshops__oxcompany->value;
    }

    /**
     * @return array associative array including capability key and active status
     */
    public function getApplePayMerchantCapabilities(): array
    {
        return array_merge(self::APPLE_PAY_MERCHANT_CAPABILITIES, $this->getSettingValue('applepay_merchant_capabilities'));
    }

    /**
     * @return array array of active capability keys
     */
    public function getActiveApplePayMerchantCapabilities(): array
    {
        return array_keys(array_filter($this->getApplePayMerchantCapabilities(), 'self::isActiveSetting'));
    }

    /**
     * @return array associative array including network key and active status
     */
    public function getApplePayNetworks(): array
    {
        return array_merge(self::APPLE_PAY_NETWORKS, $this->getSettingValue('applepay_networks'));
    }

    /**
     * @return array array of active network keys
     */
    public function getActiveApplePayNetworks(): array
    {
        return array_keys(array_filter($this->getApplePayNetworks(), 'self::isActiveSetting'));
    }

    /**
     * @return string
     */
    public function getApplePayMerchantIdentifier(): string
    {
        return $this->getSettingValue('applepay_merchant_identifier');
    }

    /**
     * @return string
     * @throws FileException
     */
    public function getApplePayMerchantCert(): string
    {
        $path = $this->getApplePayMerchantCertFilePath();
        if (file_exists($path)) {
            return file_get_contents($path);
        }

        return '';
    }

    /**
     * @return string
     * @throws FileException
     */
    public function getApplePayMerchantCertKey(): string
    {
        $path = $this->getApplePayMerchantCertKeyFilePath();
        if (file_exists($path)) {
            return file_get_contents($path);
        }

        return '';
    }

    /**
     * @param array $capabilities
     * @return void
     */
    public function saveApplePayMerchantCapabilities(array $capabilities): void
    {
        $this->saveSetting('applepay_merchant_capabilities', $capabilities);
    }

    /**
     * @return string
     * @throws FileException
     */
    public function getApplePayMerchantCertFilePath(): string
    {
        return $this->getFilesPath() . '/.merchant_cert.' . Registry::getConfig()->getShopId();
    }

    /**
     * @return string
     * @throws FileException
     */
    public function getApplePayMerchantCertKeyFilePath(): string
    {
        return $this->getFilesPath() . '/.merchant_cert_key.' . Registry::getConfig()->getShopId();
    }

    /**
     * @return string
     * @throws FileException
     */
    public function getFilesPath(): string
    {
        $path = Registry::get(ViewConfig::class)->getModulePath(Module::MODULE_ID) . '/' . 'files';

        if (!file_exists($path) && !mkdir($path, 0755, true) && !is_dir($path)) {
            throw new RuntimeException();
        }

        return $path;
    }

    /**
     * @param array $networks
     * @return void
     */
    public function saveApplePayNetworks(array $networks): void
    {
        $this->saveSetting('applepay_networks', $networks);
    }

    private function saveSetting(string $name, array $setting): void
    {
        $this->moduleSettingBridge->save($name, $setting, Module::MODULE_ID);
    }

    /**
     * @return mixed
     */
    private function getSettingValue(string $key)
    {
        return $this->moduleSettingBridge->get($key, Module::MODULE_ID);
    }

    /**
     * Intended to be used as callback function
     *
     * @param $active
     * @return bool
     */
    private static function isActiveSetting($active): bool
    {
        return $active === '1';
    }
}
