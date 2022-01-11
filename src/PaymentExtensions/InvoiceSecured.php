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

    public function execute($userModel, $basketModel): bool
    {
        if ($birthdate = Registry::getRequest()->getRequestParameter('birthdate')) {
            $userModel->oxuser__oxbirthdate = new Field($birthdate, FieldAlias::T_RAW);
        }

        $inv_secured = $this->getUnzerPaymentTypeObject();

        $customer = $this->unzerService->getUnzerCustomer($userModel);

        $uzrBasket = $this->unzerService->getUnzerBasket($this->unzerOrderId, $basketModel);

        $transaction = $inv_secured->charge(
            $basketModel->getPrice()->getPrice(),
            $basketModel->getBasketCurrency()->name,
            $this->unzerService->prepareRedirectUrl(),
            $customer,
            $this->unzerOrderId,
            $this->unzerService->getShopMetadata($this->paymentMethod),
            $uzrBasket
        );

        $this->unzerService->setSessionVars($transaction);

        $userModel->save();

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
