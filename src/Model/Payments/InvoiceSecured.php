<?php

namespace OxidSolutionCatalysts\Unzer\Model\Payments;

use OxidEsales\Eshop\Application\Model\Payment;

class InvoiceSecured extends UnzerPayment
{
    /**
     * @var mixed|Payment
     */
    protected $_oPayment;

    public function __construct($oxpaymentid)
    {
        $oPayment = oxNew(Payment::class);
        $oPayment->load($oxpaymentid);
        $this->_oPayment = $oPayment;
    }

    /**
     * @return string
     */
    public function getID(): string
    {
        return $this->_oPayment->getId();
    }


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
