<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

declare(strict_types=1);

namespace OxidSolutionCatalysts\Unzer\PaymentExtensions;

use UnzerSDK\Resources\PaymentTypes\BasePaymentType;

class ApplePay extends UnzerPayment
{
    protected string $paymentMethod = 'applepay';

    protected bool $needPending = true;

    protected bool $ajaxResponse = true;

    /**
     * @throws \Exception
     */
    public function getUnzerPaymentTypeObject(): BasePaymentType
    {
        return $this->unzerSDK->fetchPaymentType(
            $this->unzerService->getUnzerPaymentIdFromRequest()
        );
    }
}
