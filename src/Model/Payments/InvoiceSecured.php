<?php

namespace OxidSolutionCatalysts\Unzer\Model\Payments;

class InvoiceSecured extends UnzerPayment
{
    /**
     * @var string
     */
    protected string $Paymentmethod = 'invoice-secured';

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
}
