<?php

namespace OxidSolutionCatalysts\Unzer\PaymentExtensions;

use OxidEsales\Eshop\Core\Field;
use OxidEsales\Eshop\Core\Registry;
use OxidEsales\EshopCommunity\Core\Field as FieldAlias;
use UnzerSDK\Resources\PaymentTypes\BasePaymentType;

class InvoiceSecured extends UnzerPayment
{
    protected $paymentMethod = 'invoice-secured';

    protected $allowedCurrencies = ['EUR'];

    public function execute(): bool
    {
        $user = $this->session->getUser();
        if ($birthdate = Registry::getRequest()->getRequestParameter('birthdate')) {
            $user->oxuser__oxbirthdate = new Field($birthdate, FieldAlias::T_RAW);
        }

        $inv_secured = $this->getUnzerPaymentTypeObject();

        $customer = $this->unzerService->getSessionCustomerData();
        $basket = $this->session->getBasket();

        $uzrBasket = $this->unzerService->getUnzerBasket($this->unzerOrderId, $basket);

        $transaction = $inv_secured->charge(
            $basket->getPrice()->getPrice(),
            $basket->getBasketCurrency()->name,
            $this->unzerService->prepareRedirectUrl(self::CONTROLLER_URL),
            $customer,
            $this->unzerOrderId,
            $this->getMetadata(),
            $uzrBasket
        );

        $this->setSessionVars($transaction);

        $user->save();

        return true;
    }

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
