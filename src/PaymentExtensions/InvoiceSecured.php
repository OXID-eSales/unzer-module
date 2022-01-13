<?php

namespace OxidSolutionCatalysts\Unzer\PaymentExtensions;

use UnzerSDK\Resources\PaymentTypes\BasePaymentType;

class InvoiceSecured extends UnzerPayment
{
    protected $paymentMethod = 'invoice-secured';

    protected $allowedCurrencies = ['EUR'];

    /**
     * @return \UnzerSDK\Resources\PaymentTypes\InvoiceSecured
     */
    public function getUnzerPaymentTypeObject(): BasePaymentType
    {
        return $this->unzerSDK->createPaymentType(
            new \UnzerSDK\Resources\PaymentTypes\InvoiceSecured()
        );
    }
}
