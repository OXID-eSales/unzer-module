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

    public function isSelectedCurrencyAllowed(?array $allowedCurrencies): bool
    {
        return $allowedCurrencies === null
            || in_array($this->moduleContext->getActiveCurrencyName(), $allowedCurrencies);
    }
}
