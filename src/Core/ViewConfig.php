<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

namespace OxidSolutionCatalysts\Unzer\Core;

use OxidEsales\Eshop\Core\Registry;
use OxidSolutionCatalysts\Unzer\Service\ModuleSettings;
use OxidSolutionCatalysts\Unzer\Traits\ServiceContainer;

/**
 * @SuppressWarnings(PHPMD.LongVariable)
 */
class ViewConfig extends ViewConfig_parent
{
    use ServiceContainer;

    /**
     * is this a "Flow"-Theme Compatible Theme?
     * @var bool $isFlowCompatibleTheme
     */
    protected $isFlowCompatibleTheme = null;

    /**
     * is this a "Wave"-Theme Compatible Theme?
     * @var bool $isWaveCompatibleTheme
     */
    protected $isWaveCompatibleTheme = null;

    /** @var null|ModuleSettings $moduleSettings */
    protected $moduleSettings = null;

    /**
     * Returns System Mode live|sandbox.
     *
     * @return string
     */
    public function getUnzerSystemMode(): string
    {
        return $this->getUnzerModuleSettings()->getSystemMode();
    }

    /**
     * @return bool
     */
    public function isUnzerDebugMode(): bool
    {
        return $this->getUnzerModuleSettings()->isDebugMode();
    }

    /**
     * Returns unzer public key.
     *
     * @return string
     */
    public function getUnzerPubKey(): string
    {
        if (
            Registry::getSession()->getBasket()->getPaymentId()
            === UnzerDefinitions::INVOICE_UNZER_PAYMENT_ID
        ) {
            return $this->getUnzerModuleSettings()->getInvoicePublicKey();
        }
        if (
            Registry::getSession()->getBasket()->getPaymentId()
            === UnzerDefinitions::INSTALLMENT_UNZER_PAYLATER_PAYMENT_ID
        ) {
            return $this->getUnzerModuleSettings()->getInstallmentPublicKey();
        }

        return $this->getUnzerModuleSettings()->getStandardPublicKey();
    }

    /**
     * Returns unzer private key.
     *
     * @return string
     */
    public function getUnzerPrivKey(): string
    {
        if (
            Registry::getSession()->getBasket()->getPaymentId()
            === UnzerDefinitions::INVOICE_UNZER_PAYMENT_ID
        ) {
            return $this->getUnzerModuleSettings()->getInvoicePrivateKey();
        }
        if (
            Registry::getSession()->getBasket()->getPaymentId()
            === UnzerDefinitions::INSTALLMENT_UNZER_PAYLATER_PAYMENT_ID
        ) {
            return $this->getUnzerModuleSettings()->getInstallmentPrivateKey();
        }

        return $this->getUnzerModuleSettings()->getStandardPrivateKey();
    }

    public function getUnzerB2BPubKey(): string
    {
        $key = $this->getUnzerModuleSettings()->getInvoicePublicKey('B2B');
        return $key;
    }

    public function getUnzerB2CPubKey(): string
    {
        $key = $this->getUnzerModuleSettings()->getInvoicePublicKey();
        return $key;
    }

    /**
     * retrieve additional payment information from session
     *
     * @return string
     */
    public function getSessionPaymentInfo(): string
    {
        /** @var string $addPaymentInfo */
        $addPaymentInfo = Registry::getSession()->getVariable('additionalPaymentInformation') ?? '';
        return $addPaymentInfo;
    }

    /**
     * Returns unzer Installment Rate.
     *
     * @return float
     */
    public function getUnzerInstallmentRate(): float
    {
        return $this->getUnzerModuleSettings()->getInstallmentRate();
    }

    /**
     * checks if jQuery should be imported
     *
     * @return bool
     */
    public function useModuleJQueryInFrontend(): bool
    {
        return $this->getUnzerModuleSettings()->useModuleJQueryInFrontend();
    }

    /**
     * Template variable getter. Check if active theme is a Flow Theme Compatible Theme
     *
     * @return boolean
     */
    public function isFlowCompatibleTheme() //phpcs:ignore no return type because extended class method doesn't have it
    {
        if (is_null($this->isFlowCompatibleTheme)) {
            $this->isFlowCompatibleTheme = $this->isCompatibleTheme('flow');
        }
        return $this->isFlowCompatibleTheme;
    }

    /**
     * Template variable getter. Check if active theme is a Wave Theme Compatible Theme
     *
     * @return boolean
     */
    public function isWaveCompatibleTheme() //phpcs:ignore no return type because extended class method doesn't have it
    {
        if (is_null($this->isWaveCompatibleTheme)) {
            $this->isWaveCompatibleTheme = $this->isCompatibleTheme('wave');
        }
        return $this->isWaveCompatibleTheme;
    }

    /**
     * Template variable getter. Check if is a ??? Theme Compatible Theme
     *
     * @param string|null $themeId
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
        return $this->getUnzerModuleSettings()->isB2CEURInvoiceEligibility() ||
            $this->getUnzerModuleSettings()->isB2CCHFInvoiceEligibility();
    }

    /**
     * @return bool
     */
    public function isB2BInvoiceEligibility(): bool
    {
        return $this->getUnzerModuleSettings()->isB2BEURInvoiceEligibility() ||
            $this->getUnzerModuleSettings()->isB2BCHFInvoiceEligibility();
    }

    public function getBasketCurrencyName(): string
    {
        $basket = Registry::getSession()->getBasket();
        $currencyName = $basket->getBasketCurrency()->name;
        return $currencyName;
    }

    private function getUnzerModuleSettings(): ModuleSettings
    {
        if (is_null($this->moduleSettings)) {
            $this->moduleSettings = $this->getServiceFromContainer(ModuleSettings::class);
        }
        return $this->moduleSettings;
    }
}
