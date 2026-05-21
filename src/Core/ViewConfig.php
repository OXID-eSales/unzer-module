<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

declare(strict_types=1);

namespace OxidSolutionCatalysts\Unzer\Core;

use OxidEsales\Eshop\Core\Registry;
use OxidEsales\Eshop\Core\Theme;
use OxidSolutionCatalysts\Unzer\Service\PrePaymentBankAccountService;
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
     * @var boolean
     * @deprecated variable will be removed because it only played a role in the Smarty template engine context.
     */
    protected ?bool $isFlowCompatibleTheme = null;

    /**
     * is this a "Wave"-Theme Compatible Theme?
     * @var boolean
     * @deprecated variable will be removed because it only played a role in the Smarty template engine context.
     */
    protected ?bool $isWaveCompatibleTheme = null;

    /**
     * @inheritDoc
     */
    public function __construct()
    {
        parent::__construct();
    }

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
        $paymentId = Registry::getSession()->getBasket()->getPaymentId();
        if ($paymentId === UnzerDefinitions::INVOICE_UNZER_PAYMENT_ID) {
            return $this->getUnzerModuleSettings()->getInvoicePublicKey();
        }

        if ($paymentId === UnzerDefinitions::INSTALLMENT_UNZER_PAYLATER_PAYMENT_ID) {
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
        $paymentId = Registry::getSession()->getBasket()->getPaymentId();
        if ($paymentId === UnzerDefinitions::INVOICE_UNZER_PAYMENT_ID) {
            return  $this->getUnzerModuleSettings()->getInvoicePrivateKey();
        }
        if ($paymentId === UnzerDefinitions::INSTALLMENT_UNZER_PAYLATER_PAYMENT_ID) {
            return $this->getUnzerModuleSettings()->getInstallmentPrivateKey();
        }

        return $this->getUnzerModuleSettings()->getStandardPrivateKey();
    }

    public function getUnzerB2BPubKey(): string
    {
        $key = $this->getUnzerModuleSettings()->getInvoicePublicKey('B2B');
        return $key;
    }

    public function getUnzerB2BPrivKey(): string
    {
        $key = $this->getUnzerModuleSettings()->getInvoicePrivateKey('B2B');
        return $key;
    }

    public function getUnzerB2CPubKey(): string
    {
        $key = $this->getUnzerModuleSettings()->getInvoicePublicKey();
        return $key;
    }

    public function getUnzerB2CPrivKey(): string
    {
        $key = $this->getUnzerModuleSettings()->getInvoicePrivateKey('B2C');
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
     * Template variable getter. Check if is a Flow Theme Compatible Theme
     *
     * @return boolean
     *
     * @deprecated method will be removed because it only played a role in the Smarty template engine context.
     */
    public function isFlowCompatibleTheme(): bool
    {
        if (is_null($this->isFlowCompatibleTheme)) {
            $this->isFlowCompatibleTheme = $this->isThemeBasedOn('flow');
        }
        return $this->isFlowCompatibleTheme;
    }

    /**
     * Template variable getter. Check if is a Wave Theme Compatible Theme
     *
     * @return boolean
     *
     * @deprecated method will be removed because it only played a role in the Smarty template engine context.
     */
    public function isWaveCompatibleTheme(): bool
    {
        if (is_null($this->isWaveCompatibleTheme)) {
            $this->isWaveCompatibleTheme = $this->isThemeBasedOn('wave');
        }
        return $this->isWaveCompatibleTheme;
    }

    /**
     * Template variable getter. Check if is a ??? Theme Compatible Theme
     *
     * @param string $themeId
     *
     * @return boolean
     *
     * @deprecated method will be removed because it only played a role in the Smarty template engine context.
     */
    protected function isThemeBasedOn(string $themeId): bool
    {
        $result = false;
        if ($themeId) {
            $theme = oxNew(Theme::class);
            $theme->load($theme->getActiveThemeId());
            // check active theme or parent theme
            if (
                $theme->getActiveThemeId() === $themeId ||
                $theme->getInfo('parentTheme') === $themeId
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
        $currencyName = '';
        $basket = Registry::getSession()->getBasket();
        $currencyName = $basket->getBasketCurrency()->name;
        return $currencyName;
    }

    public function getPrePaymentIban(string $unzerOrderNumber): ?string
    {
        $prePaymentBankAccountService = $this->getPrePaymentBankAccountService();

        return $prePaymentBankAccountService->getIban($unzerOrderNumber);
    }

    public function getPrePaymentBic(string $unzerOrderNumber): ?string
    {
        $prePaymentBankAccountService = $this->getPrePaymentBankAccountService();

        return $prePaymentBankAccountService->getBic($unzerOrderNumber);
    }

    public function getPrePaymentHolder(string $unzerOrderNumber): ?string
    {
        $prePaymentBankAccountService = $this->getPrePaymentBankAccountService();

        return $prePaymentBankAccountService->getHolder($unzerOrderNumber);
    }

    public function getPrePaymentDescriptor(string $unzerOrderNumber): ?string
    {
        $prePaymentBankAccountService = $this->getPrePaymentBankAccountService();

        return $prePaymentBankAccountService->getDescriptor($unzerOrderNumber);
    }

    private function getPrePaymentBankAccountService(): PrePaymentBankAccountService
    {
        return $this->getServiceFromContainer(PrePaymentBankAccountService::class);
    }
    
    private function getUnzerModuleSettings(): ModuleSettings
    {
        return $this->getServiceFromContainer(ModuleSettings::class);
    }
}
