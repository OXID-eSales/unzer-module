<?php

namespace OxidSolutionCatalysts\Unzer\Service;

use OxidSolutionCatalysts\Unzer\Traits\Request;
use UnzerSDK\Resources\PaymentTypes\BasePaymentType;

class RequestService
{
    use Request;

    public function isSavePaymentSelectedByUserInRequest(BasePaymentType $paymentType): bool
    {
        return (
                $paymentType instanceof UnzerSDKPaymentTypeCard
                || $paymentType instanceof Paypal
                || $paymentType instanceof SepaDirectDebit
            )
            && $this->getUnzerStringRequestParameter('oscunzersavepayment');
    }
}
