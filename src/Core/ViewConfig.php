<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

namespace OxidSolutionCatalysts\Unzer\Core;

use OxidEsales\Eshop\Core\Registry;
use OxidSolutionCatalysts\Unzer\Service\ModuleSettings;
use OxidSolutionCatalysts\Unzer\Traits\ServiceContainer;
use phpDocumentor\Reflection\Types\True_;

class ViewConfig extends ViewConfig_parent
{
    use ServiceContainer;

    /**
     * is this a "Flow"-Theme Compatible Theme?
     * @param boolean
     */
    protected $isFlowCompatibleTheme = null;

    /**
     * is this a "Wave"-Theme Compatible Theme?
     * @param boolean
     */
    protected $isWaveCompatibleTheme = null;

    protected $moduleSettings;

    /**
     * @inheritDoc
     */
    public function __construct()
    {
        parent::__construct();

        /** @var ModuleSettings $this->moduleSettings */
        $this->moduleSettings = $this->getServiceFromContainer(ModuleSettings::class);
    }

    /**
     * Returns System Mode live|sandbox.
     *
     * @return string
     */
    public function getUnzerSystemMode(): string
    {
        return $this->moduleSettings->getSystemMode();
    }

    /**
     * @return bool
     */
    public function isUnzerDebugMode(): bool
    {
        return $this->moduleSettings->isDebugMode();
    }

    /**
     * Checks if module configurations are valid
     *
     * @return bool
     */
    public function checkUnzerHealth(): bool
    {
        return $this->moduleSettings->checkHealth();
    }

    /**
     * Returns unzer public key.
     *
     * @return string
     */
    public function getUnzerPubKey(): string
    {
        if (Registry::getSession()->getBasket()->getPaymentId() === UnzerDefinitions::INVOICE_UNZER_PAYMENT_ID) {
            return $this->moduleSettings->getShopPublicKeyInvoice();
        }

        return $this->moduleSettings->getShopPublicKey();
    }

    /**
     * Returns unzer private key.
     *
     * @return string
     */
    public function getUnzerPrivKey(): string
    {
        if (Registry::getSession()->getBasket()->getPaymentId() === UnzerDefinitions::INVOICE_UNZER_PAYMENT_ID) {
            return $this->moduleSettings->getShopPrivateKeyInvoice();
        }

        return $this->moduleSettings->getShopPrivateKey();
    }

    public function getUnzerB2BPubKey(): string
    {
        $key = '';
        $currencyName = $this->getBasketCurrencyName();
        if ($currencyName === 'CHF') {
            return $this->moduleSettings->getShopPublicKeyB2BInvoiceCHF();
        }

        if ($currencyName === 'EUR') {
            return $this->moduleSettings->getShopPublicKeyB2BInvoiceEUR();
        }
        return $key;
    }

    public function getUnzerB2BPrivKey(): string
    {
        $key = '';
        $currencyName = $this->getBasketCurrencyName();
        if ($currencyName === 'CHF') {
            return $this->moduleSettings->getShopPrivateKeyB2BInvoiceCHF();
        }

        if ($currencyName === 'EUR') {
            return $this->moduleSettings->getShopPrivateKeyB2BInvoiceEUR();
        }
        return $key;
    }

    public function getUnzerB2CPubKey(): string
    {
        $key = '';
        $currencyName = $this->getBasketCurrencyName();
        if ($currencyName === 'CHF') {
            $key = $this->moduleSettings->getShopPublicKeyB2CInvoiceCHF();
        }

        if ($currencyName === 'EUR') {
            $key = $this->moduleSettings->getShopPublicKeyB2CInvoiceEUR();
        }
        return $key;
    }

    public function getUnzerB2CPrivKey(): string
    {
        $key = '';
        $currencyName = $this->getBasketCurrencyName();
        if ($currencyName === 'CHF') {
            $key = $this->moduleSettings->getShopPrivateKeyB2CInvoiceCHF();
        }

        if ($currencyName === 'EUR') {
            $key = $this->moduleSettings->getShopPrivateKeyB2CInvoiceEUR();
        }
        return $key;
    }

    /**
     * retrieve additional payment information from session
     *
     * @return string
     */
    public function getSessionPaymentInfo(): string
    {
        return Registry::getSession()->getVariable('additionalPaymentInformation');
    }

    /**
     * Returns unzer Installment Rate.
     *
     * @return float
     */
    public function getUnzerInstallmentRate(): float
    {
        return $this->moduleSettings->getInstallmentRate();
    }

    /**
     * checks if jQuery should be imported
     *
     * @return bool
     */
    public function useModuleJQueryInFrontend(): bool
    {
        return $this->moduleSettings->useModuleJQueryInFrontend();
    }

    /**
     * Template variable getter. Check if is a Flow Theme Compatible Theme
     *
     * @return boolean
     */
    public function isFlowCompatibleTheme()
    {
        if (is_null($this->isFlowCompatibleTheme)) {
            $this->isFlowCompatibleTheme = $this->isCompatibleTheme('flow');
        }
        return $this->isFlowCompatibleTheme;
    }

    /**
     * Template variable getter. Check if is a Wave Theme Compatible Theme
     *
     * @return boolean
     */
    public function isWaveCompatibleTheme()
    {
        if (is_null($this->isWaveCompatibleTheme)) {
            $this->isWaveCompatibleTheme = $this->isCompatibleTheme('wave');
        }
        return $this->isWaveCompatibleTheme;
    }

    /**
     * Template variable getter. Check if is a ??? Theme Compatible Theme
     *
     * @psalm-suppress InternalMethod
     *
     * @return boolean
     */
    public function isCompatibleTheme($themeId = null)
    {
        $result = false;
        if ($themeId) {
            $theme = oxNew(\OxidEsales\Eshop\Core\Theme::class);
            $theme->load($theme->getActiveThemeId());
            // check active theme or parent theme
            if (
                $theme->getActiveThemeId() == $themeId ||
                $theme->getInfo('parentTheme') == $themeId
            ) {
                $result = true;
            }
        }
        return $result;
    }

    /**
     * @return bool
     */
    public function isB2CInvoiceEligibility(): bool
    {
        return $this->moduleSettings->isB2CInvoiceEligibility();
    }

    /**
     * @return bool
     */
    public function isB2BInvoiceEligibility(): bool
    {
        return $this->moduleSettings->isB2BInvoiceEligibility();
    }

    public function getBasketCurrencyName(): string
    {
        $currencyName = '';
        $basket = Registry::getSession()->getBasket();
        if ($basket) {
            $currencyName = $basket->getBasketCurrency()->name;
        }
        return $currencyName;
    }
}
