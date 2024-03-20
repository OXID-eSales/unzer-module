<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

declare(strict_types=1);

namespace OxidSolutionCatalysts\Unzer\Model;

class UnzerPaymentDataErrorStatus
{
    public bool|null $successful = null;
    public bool|null $processing = null;
    public bool|null $pending = null;

    public function __construct(array $arrayPaymentData)
    {
        $this->successful = $arrayPaymentData['successful'] ?? null;
        $this->processing = $arrayPaymentData['processing'] ?? null;
        $this->pending = $arrayPaymentData['pending'] ?? null;
    }
}
