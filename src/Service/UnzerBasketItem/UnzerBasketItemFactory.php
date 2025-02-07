<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

declare(strict_types=1);

namespace OxidSolutionCatalysts\Unzer\Service\UnzerBasketItem;

use UnzerSDK\Constants\BasketItemTypes;
use UnzerSDK\Resources\EmbeddedResources\BasketItem;
use OxidEsales\Eshop\Core\Registry;

class UnzerBasketItemFactory
{
    private UnzerBasketItemTitle $ubItemTitleService;

    public function __construct(UnzerBasketItemTitle $ubItemTitleService)
    {
        $this->ubItemTitleService = $ubItemTitleService;
    }

    public function create(float $voucherItemAmount): BasketItem
    {
        $unzerBasketItem = new BasketItem();
        $unzerBasketItem->setTitle($this->ubItemTitleService->getTitle($voucherItemAmount))
            ->setQuantity(1)
            ->setType(BasketItemTypes::VOUCHER)
            ->setVat(0)
            ->setAmountPerUnitGross(0.)
            ->setAmountDiscountPerUnitGross(Registry::getUtils()->fRound((string)$voucherItemAmount));

        return $unzerBasketItem;
    }
}
