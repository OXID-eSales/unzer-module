<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

declare(strict_types=1);

namespace OxidSolutionCatalysts\Unzer\Model;

class UnzerPaymentDataError
{
    public string|null $code = null;
    public string|null $customerMessage = null;
    public UnzerPaymentDataErrorStatus|null $status = null;
    public UnzerPaymentDataErrorProcessing|null $processing = null;

    /**
     * @param array $arrayPaymentData
     */
    public function __construct(array $arrayPaymentData)
    {
        $this->code = $arrayPaymentData['code'] ?? null;
        $this->customerMessage = $arrayPaymentData['customerMessage'] ?? null;
        $this->status = new UnzerPaymentDataErrorStatus(
            $arrayPaymentData['status'] ?? []
        );
        $this->processing = new UnzerPaymentDataErrorProcessing(
            $arrayPaymentData['processing'] ?? []
        );
    }
}
