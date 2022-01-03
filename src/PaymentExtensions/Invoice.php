<?php

namespace OxidSolutionCatalysts\Unzer\PaymentExtensions;

use Exception;
use UnzerSDK\Exceptions\UnzerApiException;

class Invoice extends UnzerPayment
{
    protected $paymentMethod = 'invoice';

    protected $allowedCurrencies = ['EUR'];

    /**
     * @return void
     * @throws UnzerApiException
     * @throws Exception
     */
    public function execute()
    {
        /** @var \UnzerSDK\Resources\PaymentTypes\Invoice $invoice */
        $invoice = $this->unzerSDK->createPaymentType(new \UnzerSDK\Resources\PaymentTypes\Invoice());

        $customer = $this->unzerService->getSessionCustomerData();
        $basket = $this->session->getBasket();

        $transaction = $invoice->charge(
            $basket->getPrice()->getPrice(),
            $basket->getBasketCurrency()->name,
            $this->unzerService->prepareRedirectUrl(self::CONTROLLER_URL),
            $customer,
            $this->unzerOrderId,
            $this->getMetadata()
        );

        $this->setSessionVars($transaction);
    }
}
