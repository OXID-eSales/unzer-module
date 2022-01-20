<?php

namespace OxidSolutionCatalysts\Unzer\Service;

use OxidEsales\Eshop\Application\Model\Payment;

class PaymentValidator
{
    /** @var PaymentExtensionLoader */
    protected $paymentExtensionLoader;

    /** @var Context */
    protected $moduleContext;

    public function __construct(
        PaymentExtensionLoader $paymentExtensionLoader,
        Context $moduleContext
    ) {
        $this->paymentExtensionLoader = $paymentExtensionLoader;
        $this->moduleContext = $moduleContext;
    }

    public function isUnzerPayment(Payment $payment): bool
    {
        $isUnzer = false;

        if (strpos(strtolower($payment->getId()), "oscunzer") !== false) {
            $isUnzer = true;
        }

        return $isUnzer;
    }

    public function isPaymentCurrencyAllowed(Payment $payment): bool
    {
        return $this->isSelectedCurrencyAllowed(
            $this->paymentExtensionLoader->getPaymentExtension($payment)->getPaymentCurrencies()
        );
    }

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
