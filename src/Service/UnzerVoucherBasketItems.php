<?php

namespace OxidSolutionCatalysts\Unzer\Service;

use OxidEsales\EshopCommunity\Application\Model\Basket;
use OxidSolutionCatalysts\Unzer\Service\UnzerBasketItem\UnzerBasketItemConverter;
use OxidSolutionCatalysts\Unzer\Service\UnzerBasketItem\UnzerBasketItemFactory;

class UnzerVoucherBasketItems
{
    /** @var UnzerBasketItemFactory $unzerBasketItemFactoryService */
    private $unzerBasketItemFactoryService;

    /** @var UnzerBasketItemConverter $unzerBasketItemConverter */
    private $unzerBasketItemConverterService;

    public function __construct(
        UnzerBasketItemFactory $unzerBasketItemFactoryService,
        UnzerBasketItemConverter $unzerBasketItemConverterService
    ) {
        $this->unzerBasketItemFactoryService = $unzerBasketItemFactoryService;
        $this->unzerBasketItemConverterService = $unzerBasketItemConverterService;
    }

    public function getVoucherBasketItems(Basket $basket): array
    {
        return array_merge(
            $this->getBasketItemsFromOxidDiscounts($basket) ?? [],
            $this->getBasketItemsFromOxidVouchers($basket) ?? [],
        );
    }

    private function getBasketItemsFromOxidDiscounts(Basket $basket): ?array
    {
        $discounts = $basket->getDiscounts();

        return $this->createUnzerVoucherBasketItems(
            $this->unzerBasketItemConverterService->convertDiscountsToVoucherAmounts($discounts)
        );
    }

    private function getBasketItemsFromOxidVouchers(Basket $basket): ?array
    {
        $vouchers = $basket->getVouchers();

        return $this->createUnzerVoucherBasketItems(
            $this->unzerBasketItemConverterService->convertVouchersToVoucherAmounts($vouchers)
        );
    }

    private function createUnzerVoucherBasketItems($voucherItems)
    {
        $unzerBasketItems = [];
        if (count($voucherItems)) {
            foreach ($voucherItems as $voucherItem) {
                $unzerBasketItems[] = $this->unzerBasketItemFactoryService->create($voucherItem);
            }
        }

        return count($unzerBasketItems) ? $unzerBasketItems : null;
    }
}
