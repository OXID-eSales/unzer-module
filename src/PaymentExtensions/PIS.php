<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

namespace OxidSolutionCatalysts\Unzer\PaymentExtensions;

use UnzerSDK\Exceptions\UnzerApiException;
use UnzerSDK\Resources\PaymentTypes\BasePaymentType;

class PIS extends UnzerPayment
{
    protected $paymentMethod = 'pis';

    protected $needPending = true;

    /**
     * @return BasePaymentType
     * @throws UnzerApiException
     */
    public function getUnzerPaymentTypeObject(): BasePaymentType
    {
        return $this->unzerSDK->createPaymentType(
            new \UnzerSDK\Resources\PaymentTypes\PIS()
        );
    }
}
