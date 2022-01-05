<?php

namespace OxidSolutionCatalysts\Unzer\PaymentExtensions;

use UnzerSDK\Resources\PaymentTypes\BasePaymentType;

class Invoice extends UnzerPayment
{
    protected $paymentMethod = 'invoice';

    protected $allowedCurrencies = ['EUR'];

    public function getUnzerPaymentTypeObject(): BasePaymentType
    {
        return $this->unzerSDK->createPaymentType(
            new \UnzerSDK\Resources\PaymentTypes\Invoice()
        );
    }
}
