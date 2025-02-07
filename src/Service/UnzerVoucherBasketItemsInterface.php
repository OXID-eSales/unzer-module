<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

declare(strict_types=1);

namespace OxidSolutionCatalysts\Unzer\Service;

use OxidEsales\Eshop\Application\Model\Basket;

interface UnzerVoucherBasketItemsInterface
{
    public function getVoucherBasketItems(Basket $basket): array;
}
