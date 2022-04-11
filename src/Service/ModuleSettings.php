<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

namespace OxidSolutionCatalysts\Unzer\Service;

use OxidEsales\Eshop\Core\Registry;
use OxidEsales\EshopCommunity\Core\Exception\FileException;
use OxidEsales\EshopCommunity\Internal\Framework\Module\Configuration\Bridge\ModuleConfigurationDaoBridgeInterface;
use OxidEsales\EshopCommunity\Internal\Framework\Module\Configuration\Bridge\ModuleSettingBridgeInterface;
use OxidEsales\Facts\Facts;
use OxidSolutionCatalysts\Unzer\Module;
use Exception;

class ModuleSettings
{
    public const SYSTEM_MODE_SANDBOX = 'sandbox';
    public const SYSTEM_MODE_PRODUCTION = 'production';
    public const PAYMENT_CHARGE = 'charge';
    public const PAYMENT_AUTHORIZE = 'authorize';

    public const APPLE_PAY_MERCHANT_CAPABILITIES = [
        'supportsCredit' => '1',
        'supportsDebit' => '1'
    ];

    public const APPLE_PAY_NETWORKS = [
        'maestro' => '1',
        'masterCard' => '1',
        'visa' => '1'
    ];

    /** @var ModuleSettingBridgeInterface */
    private $moduleSettingBridge;

    /** @var ModuleConfigurationDaoBridgeInterface */
    private $moduleInfoBridge;

    /**
     * @param ModuleSettingBridgeInterface $moduleSettingBridge
     */
    public function __construct(
        ModuleSettingBridgeInterface $moduleSettingBridge,
        ModuleConfigurationDaoBridgeInterface $moduleInfoBridge
    ) {
        $this->moduleSettingBridge = $moduleSettingBridge;
        $this->moduleInfoBridge = $moduleInfoBridge;
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
    public function getRegisteredWebhook(): string
    {
        return (string) $this->getSettingValue('registeredWebhook');
    }

    /**
     * @return string
     */
    public function getRegisteredWebhookId(): string
    {
        return (string) $this->getSettingValue('registeredWebhookId');
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
     * @return string
     */
    public function getModuleVersion(): string
    {
        return $this->moduleInfoBridge->get(Module::MODULE_ID)->getVersion();
    }

    /**
     * @return bool
     */
    public function isApplePayEligibility(): bool
    {
        return (
            $this->getApplePayMerchantCert() &&
            $this->getApplePayMerchantCertKey()
        );
    }

    /**
     * @return mixed
     */
    public function getApplePayLabel()
    {
        return $this->getSettingValue('applepay_label') ?:
            Registry::getConfig()->getActiveShop()->oxshops__oxcompany->value;
    }

    /**
     * @return array associative array including capability key and active status
     */
    public function getApplePayMerchantCapabilities(): array
    {
        return array_merge(
            self::APPLE_PAY_MERCHANT_CAPABILITIES,
            $this->getSettingValue('applepay_merchant_capabilities')
        );
    }

    /**
     * @return array array of active capability keys
     */
    public function getActiveApplePayMerchantCapabilities(): array
    {
        return array_keys(array_filter(
            $this->getApplePayMerchantCapabilities(),
            'self::isActiveSetting'
        ));
    }

    /**
     * @return array associative array including network key and active status
     */
    public function getApplePayNetworks(): array
    {
        return array_merge(
            self::APPLE_PAY_NETWORKS,
            $this->getSettingValue('applepay_networks')
        );
    }

    /**
     * @return array array of active network keys
     */
    public function getActiveApplePayNetworks(): array
    {
        return array_keys(array_filter(
            $this->getApplePayNetworks(),
            'self::isActiveSetting'
        ));
    }

    /**
     * @return string
     */
    public function getApplePayMerchantIdentifier(): string
    {
        return $this->getSettingValue($this->getSystemMode() . '-applepay_merchant_identifier');
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
        return $this->getFilesPath()
            . '/.applepay_merchant_cert.'
            . $this->getSystemMode()
            . '.'
            . Registry::getConfig()->getShopId();
    }

    /**
     * @return string
     * @throws FileException
     */
    public function getApplePayMerchantCertKeyFilePath(): string
    {
        return $this->getFilesPath()
            . '/.applepay_merchant_cert_key.'
            . $this->getSystemMode()
            . '.'
            . Registry::getConfig()->getShopId();
    }

    /**
     * @return string
     * @throws FileException
     */
    public function getFilesPath(): string
    {
        $facts = new Facts();
        $path = $facts->getShopRootPath() . DIRECTORY_SEPARATOR
            . 'var' . DIRECTORY_SEPARATOR
            . 'module' . DIRECTORY_SEPARATOR
            . Module::MODULE_ID . DIRECTORY_SEPARATOR
            . 'certs';

        if (!file_exists($path) && !mkdir($path, 0755, true) && !is_dir($path)) {
            throw new Exception('could not create path: ' . $path);
        }

        return $path;
    }

    /**
     * @param string $webHook
     * @return void
     */
    public function saveWebhook(string $webHook): void
    {
        $this->saveSetting('registeredWebhook', $webHook);
    }

    /**
     * @param string $webHookId
     * @return void
     */
    public function saveWebhookId(string $webHookId): void
    {
        $this->saveSetting('registeredWebhookId', $webHookId);
    }

    /**
     * @param bool $processed
     * @return void
     */
    public function saveApplePayCertsProcessed(bool $processed): void
    {
        $this->saveSetting($this->getSystemMode() . '-applepay_payment_certs_processed', $processed);
    }

    /**
     * @param array $networks
     * @return void
     */
    public function saveApplePayNetworks(array $networks): void
    {
        $this->saveSetting('applepay_networks', $networks);
    }

    private function saveSetting(string $name, $setting): void
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
