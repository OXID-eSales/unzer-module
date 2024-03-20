<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

declare(strict_types=1);

namespace OxidSolutionCatalysts\Unzer\Model;

class UnzerPaymentDataErrorProcessing
{
    public string|null $uniqueId = null;
    public string|null $shortId = null;

    public function __construct(array $arrayPaymentData)
    {
        $this->uniqueId = $arrayPaymentData['uniqueId'] ?? null;
        $this->shortId = $arrayPaymentData['shortId'] ?? null;
    }
}
