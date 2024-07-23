<?php

namespace OxidSolutionCatalysts\Unzer\Service\UnzerBasketItem;

use UnzerSDK\Constants\BasketItemTypes;
use UnzerSDK\Resources\EmbeddedResources\BasketItem;

class UnzerBasketItemFactory
{
    /** @var UnzerBasketItemTitle */
    private $unzerBasketItemTitleService;

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
            ->setAmountNet($voucherItemAmount)
            ->setAmountPerUnit($voucherItemAmount)
            ->setAmountGross($voucherItemAmount)
            ->setVat(0)
            ->setAmountPerUnitGross(0.)
            ->setAmountDiscountPerUnitGross($voucherItemAmount);

        return $unzerBasketItem;
    }
}
