<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

namespace OxidSolutionCatalysts\Unzer\Service;

use OxidEsales\Eshop\Core\Config;
use OxidEsales\Eshop\Core\Session;
use OxidEsales\EshopCommunity\Internal\Framework\Module\Configuration\Exception\ModuleConfigurationNotFoundException;
use OxidEsales\EshopCommunity\Core\Exception\FileException;
use OxidEsales\EshopCommunity\Internal\Framework\Module\Configuration\Bridge\ModuleConfigurationDaoBridgeInterface;
use OxidEsales\EshopCommunity\Internal\Framework\Module\Configuration\Bridge\ModuleSettingBridgeInterface;
use OxidEsales\Facts\Facts;
use OxidSolutionCatalysts\Unzer\Module;
use Exception;

/**
 * @SuppressWarnings(PHPMD.ExcessiveClassComplexity)
 */
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

    private ModuleSettingBridgeInterface $moduleSettingBridge;

    private ModuleConfigurationDaoBridgeInterface $moduleInfoBridge;

    private Session $session;

    private Config $config;

    /**
     * @param ModuleSettingBridgeInterface $moduleSettingBridge
     */
    public function __construct(
        ModuleSettingBridgeInterface $moduleSettingBridge,
        ModuleConfigurationDaoBridgeInterface $moduleInfoBridge,
        Session $session,
        Config $config
    ) {
        $this->moduleSettingBridge = $moduleSettingBridge;
        $this->moduleInfoBridge = $moduleInfoBridge;
        $this->session = $session;
        $this->config = $config;
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
        /** @var string $unzerPublicKey */
        $unzerPublicKey = $this->getSettingValue($this->getSystemMode() . '-UnzerPublicKey');
        return $unzerPublicKey;
    }

    /**
     * @return float
     */
    public function getInstallmentRate(): float
    {
        /** @var float $unzerOption */
        $unzerOption = $this->getSettingValue('UnzerOption_oscunzer_installment_rate');
        return $unzerOption;
    }

    /**
     * @return string
     */
    public function getShopPrivateKey(): string
    {
        /** @var string $unzerPrivateKey */
        $unzerPrivateKey = $this->getSettingValue($this->getSystemMode() . '-UnzerPrivateKey');
        return $unzerPrivateKey;
    }

    /**
     * @return bool
     */
    public function useModuleJQueryInFrontend(): bool
    {
        /** @var bool $unzerJQuery */
        $unzerJQuery = $this->getSettingValue('UnzerjQuery');
        return $unzerJQuery;
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
            $this->getApplePayMerchantCertKey() &&
            $this->hasWebhookConfiguration('shop')
        );
    }

    /**
     * @return mixed
     */
    public function getApplePayLabel()
    {
        return $this->getSettingValue('applepay_label') ?:
            $this->config->getActiveShop()->getFieldData('oxcompany');
    }

    /**
     * @return array associative array including capability key and active status
     */
    public function getApplePayMerchantCapabilities(): array
    {
        /** @var array $applepayMerchCaps */
        $applepayMerchCaps = $this->getSettingValue('applepay_merchant_capabilities');
        return array_merge(
            self::APPLE_PAY_MERCHANT_CAPABILITIES,
            $applepayMerchCaps
        );
    }

    /**
     * @return array array of active capability keys
     */
    public function getActiveApplePayMerchantCapabilities(): array
    {
        return array_keys(array_filter(
            $this->getApplePayMerchantCapabilities(),
            [$this, 'isActiveSetting']
        ));
    }

    /**
     * @return array associative array including network key and active status
     */
    public function getApplePayNetworks(): array
    {
        /** @var array $applepayNetworks */
        $applepayNetworks = $this->getSettingValue('applepay_networks');
        return array_merge(
            self::APPLE_PAY_NETWORKS,
            $applepayNetworks
        );
    }

    /**
     * @return array array of active network keys
     */
    public function getActiveApplePayNetworks(): array
    {
        return array_keys(array_filter(
            $this->getApplePayNetworks(),
            [$this, 'isActiveSetting']
        ));
    }

    /**
     * @return string
     */
    public function getApplePayMerchantIdentifier(): string
    {
        /** @var string $applepayMerchId */
        $applepayMerchId =
            $this->getSettingValue($this->getSystemMode() . '-applepay_merchant_identifier');
        return $applepayMerchId;
    }

    /**
     * @return string
     * @throws FileException
     */
    public function getApplePayMerchantCert(): string
    {
        $path = $this->getApplePayMerchantCertFilePath();
        if (file_exists($path)) {
            /** @var string $fileContest */
            $fileContest = file_get_contents($path);
            return $fileContest;
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
            /** @var string $fileContest */
            $fileContest = file_get_contents($path);
            return $fileContest;
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
            . $this->config->getShopId();
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
            . $this->config->getShopId();
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
     * @param string $paymentKeyId
     * @return void
     */
    public function saveApplePayPaymentKeyId(string $paymentKeyId): void
    {
        $this->saveSetting($this->getSystemMode() . 'ApplePayPaymentKeyId', $paymentKeyId);
    }

    /**
     * @param string $certificateId
     * @return void
     */
    public function saveApplePayPaymentCertificateId(string $certificateId): void
    {
        $this->saveSetting($this->getSystemMode() . 'ApplePayPaymentCertificateId', $certificateId);
    }

    /**
     * @return string
     */
    public function getApplePayPaymentKeyId(): string
    {
        /** @var string $paymentKeyId */
        $paymentKeyId = $this->getSettingValue($this->getSystemMode() . 'ApplePayPaymentKeyId');
        return $paymentKeyId;
    }

    /**
     * @return string
     */
    public function getApplePayPaymentCertificateId(): string
    {
        /** @var string $certificateId */
        $certificateId = $this->getSettingValue($this->getSystemMode() . 'ApplePayPaymentCertificateId');
        return $certificateId;
    }

    /**
     * @param array $networks
     * @return void
     */
    public function saveApplePayNetworks(array $networks): void
    {
        $this->saveSetting('applepay_networks', $networks);
    }

    /**
     * @param array $webhookConfig
     * @return void
     */
    public function saveWebhookConfiguration(array $webhookConfig): void
    {
        $this->moduleSettingBridge->save('webhookConfiguration', $webhookConfig, Module::MODULE_ID);
    }

    /**
     * @return array
     */
    public function getWebhookConfiguration(): array
    {
        return $this->moduleSettingBridge->get('webhookConfiguration', Module::MODULE_ID);
    }

    /**
     * @return array
     */
    public function getPrivateKeysWithContext(): array
    {
        $privateKeys = [];
        if ('' !==  $this->getShopPrivateKey()) {
            $privateKeys['shop'] = $this->getShopPrivateKey();
        }
        if ('' !== $this->getShopPrivateKeyB2CInvoiceEUR()) {
            $privateKeys['b2ceur'] = $this->getShopPrivateKeyB2CInvoiceEUR();
        }
        if ('' !== $this->getShopPrivateKeyB2CInvoiceCHF()) {
            $privateKeys['b2cchf'] = $this->getShopPrivateKeyB2CInvoiceCHF();
        }
        if ('' !== $this->getShopPrivateKeyB2BInvoiceEUR()) {
            $privateKeys['b2beur'] = $this->getShopPrivateKeyB2BInvoiceEUR();
        }
        if ('' !== $this->getShopPrivateKeyB2BInvoiceCHF()) {
            $privateKeys['b2bchf'] = $this->getShopPrivateKeyB2BInvoiceCHF();
        }
        return $privateKeys;
    }

    /**
     * @param string $name
     * @param bool|int|string|array $setting
     * @return void
     */
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
     * @param bool|int|string $active
     * @return bool
     *
     * @SuppressWarnings(PHPMD.UnusedPrivateMethod)
     */
    private function isActiveSetting($active): bool
    {
        return $active === '1';
    }

    /**
     * @return bool
     */
    public function isStandardEligibility(): bool
    {
        return (
            $this->getShopPrivateKey() &&
            $this->getShopPublicKey() &&
            $this->hasWebhookConfiguration('shop')
        );
    }

    /**
     * @return bool
     */
    public function isInvoiceEligibility(): bool
    {
        return (
                $this->isB2CInvoiceEligibility() &&
                $this->hasWebhookConfiguration('b2ceur') &&
                $this->hasWebhookConfiguration('b2cchf')
            ||
                $this->isB2BInvoiceEligibility() &&
                $this->hasWebhookConfiguration('b2beur') &&
                $this->hasWebhookConfiguration('b2bchf')
        );
    }

    /**
     * @return bool
     */
    public function isB2CInvoiceEligibility(): bool
    {
        return (
            $this->isBasketCurrencyCHF() &&
            !empty($this->getShopPublicKeyB2CInvoiceCHF()) &&
            !empty($this->getShopPrivateKeyB2CInvoiceCHF())
        ) ||
        (
            $this->isBasketCurrencyEUR() &&
            !empty($this->getShopPublicKeyB2CInvoiceEUR()) &&
            !empty($this->getShopPrivateKeyB2CInvoiceEUR())
        );
    }

    /**
     * @return bool
     */
    public function isB2BInvoiceEligibility(): bool
    {
        return (
            $this->isBasketCurrencyCHF() &&
            !empty($this->getShopPublicKeyB2BInvoiceCHF()) &&
            !empty($this->getShopPrivateKeyB2BInvoiceCHF())
        ) ||
        (
            $this->isBasketCurrencyEUR() &&
            !empty($this->getShopPublicKeyB2BInvoiceEUR()) &&
            !empty($this->getShopPrivateKeyB2BInvoiceEUR())
        );
    }

    /**
     * @return string
     */
    private function getShopPublicKeyB2CInvoiceEUR(): string
    {
        /** @var string $unzerPubKeyB2CEUR */
        $unzerPubKeyB2CEUR = $this->getSettingValue($this->getSystemMode() . '-UnzerPublicKeyB2CEUR');
        return $unzerPubKeyB2CEUR;
    }

    /**
     * @return string
     */
    private function getShopPrivateKeyB2CInvoiceEUR(): string
    {
        /** @var string $unzerPrivKeyB2CEUR */
        $unzerPrivKeyB2CEUR = $this->getSettingValue($this->getSystemMode() . '-UnzerPrivateKeyB2CEUR');
        return $unzerPrivKeyB2CEUR;
    }

    /**
     * @return string
     */
    private function getShopPublicKeyB2BInvoiceEUR(): string
    {
        /** @var string $unzerPubKeyB2BEUR */
        $unzerPubKeyB2BEUR = $this->getSettingValue($this->getSystemMode() . '-UnzerPublicKeyB2BEUR');
        return $unzerPubKeyB2BEUR;
    }

    /**
     * @return string
     */
    private function getShopPrivateKeyB2BInvoiceEUR(): string
    {
        /** @var string $unzerPrivKeyB2BEUR */
        $unzerPrivKeyB2BEUR = $this->getSettingValue($this->getSystemMode() . '-UnzerPrivateKeyB2BEUR');
        return $unzerPrivKeyB2BEUR;
    }

    /**
     * @return string
     */
    private function getShopPublicKeyB2CInvoiceCHF(): string
    {
        /** @var string $unzerPubKeyB2CCHF */
        $unzerPubKeyB2CCHF = $this->getSettingValue($this->getSystemMode() . '-UnzerPublicKeyB2CCHF');
        return $unzerPubKeyB2CCHF;
    }

    /**
     * @return string
     */
    private function getShopPrivateKeyB2CInvoiceCHF(): string
    {
        /** @var string $unzerPrivKeyB2CCHF */
        $unzerPrivKeyB2CCHF = $this->getSettingValue($this->getSystemMode() . '-UnzerPrivateKeyB2CCHF');
        return $unzerPrivKeyB2CCHF;
    }

    /**
     * @return string
     */
    private function getShopPublicKeyB2BInvoiceCHF(): string
    {
        /** @var string $unzerPubKeyB2BCHF */
        $unzerPubKeyB2BCHF = $this->getSettingValue($this->getSystemMode() . '-UnzerPublicKeyB2BCHF');
        return $unzerPubKeyB2BCHF;
    }

    /**
     * @return string
     */
    private function getShopPrivateKeyB2BInvoiceCHF(): string
    {
        /** @var string $unzerPrivKeyB2BCHF */
        $unzerPrivKeyB2BCHF = $this->getSettingValue($this->getSystemMode() . '-UnzerPrivateKeyB2BCHF');
        return $unzerPrivKeyB2BCHF;
    }

    /**
     * @param string $customerType
     * @return string
     */
    public function getShopPublicKeyInvoice(string $customerType = 'B2C'): string
    {
        $result = $this->getShopPublicKey();

        if ($this->isB2CInvoiceEligibility() && $customerType === 'B2C') {
            if ($this->isBasketCurrencyCHF()) {
                $result = $this->getShopPublicKeyB2CInvoiceCHF();
            }
            if ($this->isBasketCurrencyEUR()) {
                $result = $this->getShopPublicKeyB2CInvoiceEUR();
            }
        }

        if ($this->isB2BInvoiceEligibility() && $customerType === 'B2B') {
            if ($this->isBasketCurrencyCHF()) {
                $result = $this->getShopPublicKeyB2BInvoiceCHF();
            }
            if ($this->isBasketCurrencyEUR()) {
                $result = $this->getShopPublicKeyB2BInvoiceEUR();
            }
        }

        return $result;
    }

    /**
     * @param string $customerType
     * @return string
     */
    public function getShopPrivateKeyInvoice(string $customerType = 'B2C'): string
    {
        $result = $this->getShopPrivateKey();

        if ($this->isB2CInvoiceEligibility() && $customerType === 'B2C') {
            if ($this->isBasketCurrencyCHF()) {
                $result = $this->getShopPrivateKeyB2CInvoiceCHF();
            }
            if ($this->isBasketCurrencyEUR()) {
                $result = $this->getShopPrivateKeyB2CInvoiceEUR();
            }
        }

        if ($this->isB2BInvoiceEligibility() && $customerType === 'B2B') {
            if ($this->isBasketCurrencyCHF()) {
                $result = $this->getShopPrivateKeyB2BInvoiceCHF();
            }
            if ($this->isBasketCurrencyEUR()) {
                $result = $this->getShopPrivateKeyB2BInvoiceEUR();
            }
        }

        return $result;
    }

    /**
     * @param string $customerType
     * @param string $currency
     * @return string
     */
    public function getShopPublicKeyInvoiceByCustomerTypeAndCurrency(string $customerType, string $currency): string
    {
        $key = '';
        if ($customerType == 'B2C' && $currency == 'EUR') {
            $key = $this->getShopPublicKeyB2CInvoiceEUR();
        } elseif ($customerType == 'B2C' && $currency == 'CHF') {
            $key = $this->getShopPublicKeyB2CInvoiceCHF();
        } elseif ($customerType == 'B2B' && $currency == 'EUR') {
            $key = $this->getShopPublicKeyB2BInvoiceEUR();
        } elseif ($customerType == 'B2B' && $currency == 'CHF') {
            $key = $this->getShopPublicKeyB2BInvoiceCHF();
        }
        return $key;
    }

    /**
     * @param string $customerType
     * @param string $currency
     * @return string
     */
    public function getShopPrivateKeyInvoiceByCustomerTypeAndCurrency(string $customerType, string $currency): string
    {
        $key = '';
        if ($customerType == 'B2C' && $currency == 'EUR') {
            $key = $this->getShopPrivateKeyB2CInvoiceEUR();
        } elseif ($customerType == 'B2C' && $currency == 'CHF') {
            $key = $this->getShopPrivateKeyB2CInvoiceCHF();
        } elseif ($customerType == 'B2B' && $currency == 'EUR') {
            $key = $this->getShopPrivateKeyB2BInvoiceEUR();
        } elseif ($customerType == 'B2B' && $currency == 'CHF') {
            $key = $this->getShopPrivateKeyB2BInvoiceCHF();
        }
        return $key;
    }

    /**
     * @return bool
     */
    private function isBasketCurrencyCHF(): bool
    {
        return $this->getBasketCurrency() === 'CHF';
    }

    /**
     * @return bool
     */
    private function isBasketCurrencyEUR(): bool
    {
        return $this->getBasketCurrency() === 'EUR';
    }

    /**
     * @return string
     */
    private function getBasketCurrency(): string
    {
        return $this->session->getBasket()->getBasketCurrency()->name;
    }

    /**
     * @param $context
     * @return bool
     */
    private function hasWebhookConfiguration(string $context): bool
    {
        $privateKeysContext = $this->getPrivateKeysWithContext();
        return (!empty($privateKeysContext[$context]));
    }
}
