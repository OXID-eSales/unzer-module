<?php

namespace OxidSolutionCatalysts\Unzer\Model\Payments;

class InvoiceSecured extends UnzerPayment
{
    /**
     * @var mixed|\OxidEsales\Eshop\Application\Model\Payment
     */
    protected $_oPayment;

    public function __construct($oxpaymentid)
    {
        $oPayment = oxNew(\OxidEsales\Eshop\Application\Model\Payment::class);
        $oPayment->load($oxpaymentid);
        $this->_oPayment = $oPayment;
    }

    /**
     * @return string
     */
    public function getPaymentMethod(): string
    {
        return 'invoice-secured';
    }

    /**
     * @return string
     */
    public function getPaymentCode(): string
    {
        return 'IV';
    }

    /**
     * @return string
     */
    public function getSyncMode(): string
    {
        return 'SYNC';
    }

    /**
     * @return string
     */
    public function getID(): string
    {
        return $this->_oPayment->getId();
    }

    /**
     * @return string
     */
    public function getPaymentProcedure(): string
    {
        return $this->_oPayment->oxpayment__oxpaymentprocedure->value;
    }

    /**
     * @return mixed|void
     */
    public function validate()
    {
        //TODO
    }
}
