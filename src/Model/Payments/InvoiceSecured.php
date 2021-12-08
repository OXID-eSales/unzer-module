<?php

namespace OxidSolutionCatalysts\Unzer\Model\Payments;

use Exception;
use OxidEsales\Eshop\Core\Field;
use OxidEsales\Eshop\Core\Registry;
use OxidEsales\EshopCommunity\Core\Field as FieldAlias;
use OxidSolutionCatalysts\Unzer\Core\UnzerHelper;
use UnzerSDK\examples\ExampleDebugHandler;
use UnzerSDK\Exceptions\UnzerApiException;

class InvoiceSecured extends UnzerPayment
{
    /**
     * @var string
     */
    protected $Paymentmethod = 'invoice-secured';

    /**
     * @var array
     */
    protected $aCurrencies = ['EUR'];

    /**
     * @return bool
     */
    public function isRecurringPaymentType(): bool
    {
        return false;
    }

    /**
     * @return void
     * @throws UnzerApiException
     * @throws Exception
     */
    public function execute()
    {
        /** @var \UnzerSDK\Resources\PaymentTypes\InvoiceSecured $inv_secured */
        $inv_secured = $this->unzerSDK->createPaymentType(new \UnzerSDK\Resources\PaymentTypes\InvoiceSecured());

        if ($birthdate = Registry::getRequest()->getRequestParameter('birthdate')) {
            $this->user->oxuser__oxbirthdate = new Field($birthdate, FieldAlias::T_RAW);
        }

        $customer = $this->getCustomerData();

        $uzrBasket = $this->getUnzerBasket($this->basket);

        $transaction = $inv_secured->charge(
            $this->basket->getPrice()->getPrice(),
            $this->basket->getBasketCurrency()->name,
            UnzerHelper::redirecturl(self::CONTROLLER_URL),
            $customer,
            $this->unzerOrderId,
            $this->getMetadata(),
            $uzrBasket
        );

        $this->setSessionVars($transaction);
        $this->user->save();
    }
}
