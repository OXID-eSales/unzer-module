<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

namespace OxidSolutionCatalysts\Unzer\Service;

use OxidEsales\Eshop\Application\Model\Payment;
use OxidSolutionCatalysts\Unzer\Core\UnzerDefinitions;

class PaymentValidator
{
    protected PaymentExtensionLoader $paymentExtLoader;

    protected Context $moduleContext;

    protected ModuleSettings $moduleSettings;

    /**
     * @param PaymentExtensionLoader $paymentExtLoader
     * @param Context $moduleContext
     */
    public function __construct(
        PaymentExtensionLoader $paymentExtLoader,
        Context $moduleContext,
        ModuleSettings $moduleSettings
    ) {
        $this->paymentExtLoader = $paymentExtLoader;
        $this->moduleContext = $moduleContext;
        $this->moduleSettings = $moduleSettings;
    }

    /**
     * @param Payment $payment
     * @return bool
     */
    public function isUnzerPayment(Payment $payment): bool
    {
        $isUnzer = false;

        if (stripos($payment->getId(), "oscunzer") !== false) {
            $isUnzer = true;
        }

        return $isUnzer;
    }

    /**
     * @param Payment $payment
     * @return bool
     */
    public function isPaymentCurrencyAllowed(Payment $payment): bool
    {
        return $this->isSelectedCurrencyAllowed(
            $this->paymentExtLoader->getPaymentExtension($payment)->getPaymentCurrencies()
        );
    }

    /**
     * @param array $allowedCurrencies
     * @return bool
     */
    public function isSelectedCurrencyAllowed(array $allowedCurrencies): bool
    {
        return !count($allowedCurrencies)
            || in_array($this->moduleContext->getActiveCurrencyName(), $allowedCurrencies, true);
    }

    public function isSecuredPayment(Payment $payment): bool
    {
        $isSecured = false;

        if ($this->isUnzerPayment($payment)) {
            if (stripos($payment->getId(), "installment") !== false) {
                $isSecured = true;
            }

            if (stripos($payment->getId(), "secured") !== false) {
                $isSecured = true;
            }
        }

        return $isSecured;
    }

    public function isConfigurationHealthy(Payment $payment): bool
    {
        $paymentId = $payment->getId();
        $privateKeys = $this->moduleSettings->getPrivateKeysWithContext();
        $isHealthy = (!empty($privateKeys['shop'])); // default
        if ($paymentId === UnzerDefinitions::INVOICE_UNZER_PAYMENT_ID) {
            $isHealthy = (
                !empty($privateKeys['b2ceur']) &&
                !empty($privateKeys['b2cchf']) &&
                !empty($privateKeys['b2beur']) &&
                !empty($privateKeys['b2bchf'])
            );
        } elseif ($paymentId === UnzerDefinitions::APPLEPAY_UNZER_PAYMENT_ID) {
            $isHealthy = (
                !empty($this->moduleSettings->getApplePayMerchantCert()) &&
                !empty($this->moduleSettings->getApplePayMerchantCertKey()) &&
                !empty($this->moduleSettings->getApplePayMerchantIdentifier())
            );
        }

        return $isHealthy;
    }
}
