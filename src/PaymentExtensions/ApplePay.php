<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

namespace OxidSolutionCatalysts\Unzer\PaymentExtensions;

use UnzerSDK\Resources\PaymentTypes\BasePaymentType;

class ApplePay extends UnzerPayment
{
    protected $paymentMethod = 'applepay';

    protected $needPending = true;

    /**
     * @return BasePaymentType
     * @throws \Exception
     */
    public function getUnzerPaymentTypeObject(): BasePaymentType
    {
        throw new \Exception('Payment method not implemented yet');
    }
}
