<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

declare(strict_types=1);

namespace OxidSolutionCatalysts\Unzer\PaymentExtensions;

use UnzerSDK\Resources\PaymentTypes\BasePaymentType;

class Installment extends UnzerPayment
{
    protected string $paymentMethod = 'installment-secured';

    /**
     * @return BasePaymentType
     * @throws \UnzerSDK\Exceptions\UnzerApiException
     */
    public function getUnzerPaymentTypeObject(): BasePaymentType
    {
        return $this->unzerSDK->fetchPaymentType(
            $this->unzerService->getUnzerPaymentIdFromRequest()
        );
    }
}
