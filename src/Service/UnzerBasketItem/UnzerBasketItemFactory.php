<?php

namespace OxidSolutionCatalysts\Unzer\Service\UnzerBasketItem;

use UnzerSDK\Constants\BasketItemTypes;
use UnzerSDK\Resources\EmbeddedResources\BasketItem;
use OxidEsales\Eshop\Core\Registry;

/**
 * TODO: Fix all the suppressed warnings
 * @SuppressWarnings(PHPMD.LongVariable)
 */
class UnzerBasketItemFactory
{
    private UnzerBasketItemTitle $unzerBasketItemTitleService;

    public function __construct(UnzerBasketItemTitle $unzerBasketItemTitleService)
    {
        $this->unzerBasketItemTitleService = $unzerBasketItemTitleService;
    }
    public function create(float $voucherItemAmount): BasketItem
    {
        $unzerBasketItem = new BasketItem();
        $unzerBasketItem->setTitle($this->unzerBasketItemTitleService->getTitle($voucherItemAmount))
            ->setQuantity(1)
            ->setType(BasketItemTypes::VOUCHER)
            ->setVat(0)
            ->setAmountPerUnitGross(0.)
            ->setAmountDiscountPerUnitGross(Registry::getUtils()->fRound((string)$voucherItemAmount));

        return $unzerBasketItem;
    }
}
