<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

namespace OxidSolutionCatalysts\Unzer\PaymentExtensions;

use UnzerSDK\Exceptions\UnzerApiException;
use UnzerSDK\Resources\PaymentTypes\BasePaymentType;

class Card extends UnzerPayment
{
    protected $paymentMethod = 'card';

    protected $needPending = true;

    /**
     * @return BasePaymentType
     * @throws UnzerApiException
     */
    public function getUnzerPaymentTypeObject(): BasePaymentType
    {
        return $this->unzerSDK->fetchPaymentType(
            $this->unzerService->getUnzerPaymentIdFromRequest()
        );
    }
}
