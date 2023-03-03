<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

namespace OxidSolutionCatalysts\Unzer\Service;

use OxidEsales\Eshop\Core\Session;
use OxidEsales\EshopCommunity\Internal\Framework\Module\Configuration\Exception\ModuleConfigurationNotFoundException;
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

    /** @var Session */
    private Session $session;

    /**
     * @param ModuleSettingBridgeInterface $moduleSettingBridge
     */
    public function __construct(
        ModuleSettingBridgeInterface $moduleSettingBridge,
        ModuleConfigurationDaoBridgeInterface $moduleInfoBridge,
        Session $session
    ) {
        $this->moduleSettingBridge = $moduleSettingBridge;
        $this->moduleInfoBridge = $moduleInfoBridge;
        $this->session = $session;
    }

    /**
     * Checks if module configurations are valid
     */
    public function checkHealth(): bool
    {
        return (
            $this->getShopPublicKey() &&
            $this->getShopPrivateKey() &&
            $this->getRegisteredWebhookId()
        );
    }

    /**
     * @return bool
     */
    public function isDebugMode(): bool
    {
        return $this->getSettingValue('UnzerDebug') === true;
    }

    /**
     * @return bool
     */
    public function isSandboxMode(): bool
    {
        return $this->getSystemMode() === self::SYSTEM_MODE_SANDBOX;
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
     * @param string $systemMode
     * @return void
     */
    public function setSystemMode(string $systemMode): void
    {
        $this->saveSetting('UnzerSystemMode', $systemMode);
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
            $this->session->getActiveShop()->oxshops__oxcompany->value;
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
        return (string) $this->getSettingValue($this->getSystemMode() . '-applepay_merchant_identifier');
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
            . $this->session->getShopId();
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
            . $this->session->getShopId();
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
     * @param string $id
     * @return void
     */
    public function saveApplePayPaymentKeyId(string $id): void
    {
        $this->saveSetting($this->getSystemMode() . 'ApplePayPaymentKeyId', $id);
    }

    /**
     * @param string $id
     * @return void
     */
    public function saveApplePayPaymentCertificateId(string $id): void
    {
        $this->saveSetting($this->getSystemMode() . 'ApplePayPaymentCertificateId', $id);
    }

    /**
     * @return string
     */
    public function getApplePayPaymentKeyId(): string
    {
        return $this->getSettingValue($this->getSystemMode() . 'ApplePayPaymentKeyId');
    }

    /**
     * @return string
     */
    public function getApplePayPaymentCertificateId(): string
    {
        return $this->getSettingValue($this->getSystemMode() . 'ApplePayPaymentCertificateId');
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
        $result = '';
        try {
            $result = $this->moduleSettingBridge->get($key, Module::MODULE_ID);
        } catch (ModuleConfigurationNotFoundException $exception) {
            //todo: improve
        }
        return $result;
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

    /**
     * @return bool
     */
    public function isInvoiceEligibility(): bool
    {
        return (
            $this->isB2CInvoiceEligibility() ||
            $this->isB2BInvoiceEligibility()
        );
    }

    /**
     * @return bool
     */
    public function isB2CInvoiceEligibility(): bool
    {
        return (
            $this->isBasketCurrencyCHF() &&
            $this->getShopPublicKeyB2CInvoiceCHF() &&
            $this->getShopPrivateKeyB2CInvoiceCHF()
        ) ||
        (
            $this->isBasketCurrencyEUR() &&
            $this->getShopPublicKeyB2CInvoiceEUR() &&
            $this->getShopPrivateKeyB2CInvoiceEUR()
        );
    }

    /**
     * @return bool
     */
    public function isB2BInvoiceEligibility(): bool
    {
        return (
            $this->isBasketCurrencyCHF() &&
            $this->getShopPublicKeyB2BInvoiceCHF() &&
            $this->getShopPrivateKeyB2BInvoiceCHF()
        ) ||
        (
            $this->isBasketCurrencyEUR() &&
            $this->getShopPublicKeyB2BInvoiceEUR() &&
            $this->getShopPrivateKeyB2BInvoiceEUR()
        );
    }

    /**
     * @return string
     */
    public function getShopPublicKeyB2CInvoiceEUR(): string
    {
        return (string)$this->getSettingValue($this->getSystemMode() . '-UnzerPublicKeyB2CEUR');
    }

    /**
     * @return string
     */
    public function getShopPrivateKeyB2CInvoiceEUR(): string
    {
        return (string)$this->getSettingValue($this->getSystemMode() . '-UnzerPrivateKeyB2CEUR');
    }

    /**
     * @return string
     */
    public function getShopPublicKeyB2BInvoiceEUR(): string
    {
        return (string)$this->getSettingValue($this->getSystemMode() . '-UnzerPublicKeyB2BEUR');
    }

    /**
     * @return string
     */
    public function getShopPrivateKeyB2BInvoiceEUR(): string
    {
        return (string)$this->getSettingValue($this->getSystemMode() . '-UnzerPrivateKeyB2BEUR');
    }

    /**
     * @return string
     */
    public function getShopPublicKeyB2CInvoiceCHF(): string
    {
        return (string)$this->getSettingValue($this->getSystemMode() . '-UnzerPublicKeyB2CCHF');
    }

    /**
     * @return string
     */
    public function getShopPrivateKeyB2CInvoiceCHF(): string
    {
        return (string)$this->getSettingValue($this->getSystemMode() . '-UnzerPrivateKeyB2CCHF');
    }

    /**
     * @return string
     */
    public function getShopPublicKeyB2BInvoiceCHF(): string
    {
        return (string)$this->getSettingValue($this->getSystemMode() . '-UnzerPublicKeyB2BCHF');
    }

    /**
     * @return string
     */
    public function getShopPrivateKeyB2BInvoiceCHF(): string
    {
        return (string)$this->getSettingValue($this->getSystemMode() . '-UnzerPrivateKeyB2BCHF');
    }

    /**
     * @return string
     */
    public function getShopPublicKeyInvoice(): string
    {
        if ($this->isB2CInvoiceEligibility()) {
            if ($this->isBasketCurrencyCHF()) {
                return $this->getShopPublicKeyB2CInvoiceCHF();
            }

            return $this->getShopPublicKeyB2CInvoiceEUR();
        }

        if ($this->isB2BInvoiceEligibility()) {
            if ($this->isBasketCurrencyCHF()) {
                return $this->getShopPublicKeyB2BInvoiceCHF();
            }

            return $this->getShopPublicKeyB2BInvoiceEUR();
        }

        return '';
    }

    /**
     * @return string
     */
    public function getShopPrivateKeyInvoice(): string
    {
        if ($this->isB2CInvoiceEligibility()) {
            if ($this->isBasketCurrencyCHF()) {
                return $this->getShopPrivateKeyB2CInvoiceCHF();
            }

            return $this->getShopPrivateKeyB2CInvoiceEUR();
        }

        if ($this->isB2BInvoiceEligibility()) {
            if ($this->isBasketCurrencyCHF()) {
                return $this->getShopPrivateKeyB2BInvoiceCHF();
            }

            return $this->getShopPrivateKeyB2BInvoiceEUR();
        }

        return '';
    }

    /**
     * @return bool
     */
    public function isBasketCurrencyCHF(): bool
    {
        return $this->getBasketCurrency() === 'CHF';
    }

    /**
     * @return bool
     */
    public function isBasketCurrencyEUR(): bool
    {
        return $this->getBasketCurrency() === 'EUR';
    }

    /**
     * @return string
     */
    public function getBasketCurrency(): string
    {
        return $this->session->getBasket()->getBasketCurrency()->name;
    }
}
