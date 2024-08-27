<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

declare(strict_types=1);

namespace OxidSolutionCatalysts\Unzer\PaymentExtensions;

use UnzerSDK\Exceptions\UnzerApiException;
use UnzerSDK\Resources\PaymentTypes\BasePaymentType;
use UnzerSDK\Resources\PaymentTypes\Paypal as UnzerPaypal;

class PayPal extends UnzerPayment
{
    protected string $paymentMethod = 'paypal';

    protected bool $needPending = true;

    /**
     * @return \UnzerSDK\Interfaces\UnzerParentInterface
     * @throws UnzerApiException
     */
    public function getUnzerPaymentTypeObject(): \UnzerSDK\Interfaces\UnzerParentInterface
    {
        return $this->unzerSDK->createPaymentType(
            new UnzerPaypal()
        );
    }
}
