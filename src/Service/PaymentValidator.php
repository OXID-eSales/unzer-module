<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

namespace OxidSolutionCatalysts\Unzer\Service;

use OxidEsales\Eshop\Application\Model\Payment;

class PaymentValidator
{
    /** @var PaymentExtensionLoader */
    protected $paymentExtLoader;

    /** @var Context */
    protected $moduleContext;

    /**
     * @param PaymentExtensionLoader $paymentExtLoader
     * @param Context $moduleContext
     */
    public function __construct(
        PaymentExtensionLoader $paymentExtLoader,
        Context $moduleContext
    ) {
        $this->paymentExtLoader = $paymentExtLoader;
        $this->moduleContext = $moduleContext;
    }

    /**
     * @param Payment $payment
     * @return bool
     */
    public function isUnzerPayment(Payment $payment): bool
    {
        $isUnzer = false;

        if (strpos(strtolower($payment->getId()), "oscunzer") !== false) {
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
            || in_array($this->moduleContext->getActiveCurrencyName(), $allowedCurrencies);
    }

    public function isSecuredPayment(Payment $payment): bool
    {
        $isSecured = false;

        if ($this->isUnzerPayment($payment)) {
            if (strpos(strtolower($payment->getId()), "installment") !== false) {
                $isSecured = true;
            }

            if (strpos(strtolower($payment->getId()), "secured") !== false) {
                $isSecured = true;
            }
        }

        return $isSecured;
    }
}
