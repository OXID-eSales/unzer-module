<?php

namespace OxidSolutionCatalysts\Unzer\Model\Payments;

use OxidEsales\Eshop\Application\Model\Payment;

class InvoiceSecured extends UnzerPayment
{
    /**
     * @return bool
     */
    public function isRecurringPaymentType(): bool
    {
        return false;
    }

    /**
     * @return mixed|void
     */
    public function execute()
    {
        //TODO
    }

    /**
     * @return string
     */
    public function getPaymentMethod(): string
    {
        return 'invoice-secured';
    }
}
