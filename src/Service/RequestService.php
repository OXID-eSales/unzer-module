<?php

namespace OxidSolutionCatalysts\Unzer\Service;

use OxidSolutionCatalysts\Unzer\Traits\Request;
use UnzerSDK\Resources\PaymentTypes\BasePaymentType;
use UnzerSDK\Resources\PaymentTypes\Card;
use UnzerSDK\Resources\PaymentTypes\Paypal;
use UnzerSDK\Resources\PaymentTypes\SepaDirectDebit;

class RequestService
{
    use Request;

    public function isSavePaymentSelectedByUserInRequest(BasePaymentType $paymentType): bool
    {
        return (
                $paymentType instanceof Card
                || $paymentType instanceof Paypal
                || $paymentType instanceof SepaDirectDebit
            )
            && $this->getUnzerStringRequestParameter('oscunzersavepayment');
    }
}
