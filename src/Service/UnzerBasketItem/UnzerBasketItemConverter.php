<?php

namespace OxidSolutionCatalysts\Unzer\Service\UnzerBasketItem;

class UnzerBasketItemConverter
{
    public function convertDiscountsToVoucherAmounts(array &$discountItems): array
    {
        return array_map(
            function ($discountItem) {
                return $discountItem->dDiscount;
            },
            $discountItems
        );
    }

    public function convertVouchersToVoucherAmounts(array &$voucherItems): array
    {
        return array_map(
            function ($voucherItem) {
                return $voucherItem->dVoucherdiscount;
            },
            $voucherItems
        );
    }
}
