<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

namespace OxidSolutionCatalysts\Unzer\Tests\Unit\Service;

use OxidSolutionCatalysts\Unzer\Service\UnzerVoucherBasketItems;
use OxidSolutionCatalysts\Unzer\Service\UnzerBasketItem\UnzerBasketItemConverter;
use OxidSolutionCatalysts\Unzer\Service\UnzerBasketItem\UnzerBasketItemFactory;
use OxidEsales\EshopCommunity\Application\Model\Basket;
use UnzerSDK\Resources\EmbeddedResources\BasketItem;
use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\MockObject\MockObject;

class UnzerVoucherBasketItemsTest extends TestCase
{
    /** @var MockObject|UnzerBasketItemFactory */
    private $unzerBasketItemFactoryService;

    /** @var MockObject|UnzerBasketItemConverter */
    private $unzerBasketItemConverterService;

    /** @var UnzerVoucherBasketItems */
    private $unzerVoucherBasketItems;

    protected function setUp(): void
    {
        $this->unzerBasketItemFactoryService = $this->createMock(UnzerBasketItemFactory::class);
        $this->unzerBasketItemConverterService = $this->createMock(UnzerBasketItemConverter::class);

        $this->unzerVoucherBasketItems = new UnzerVoucherBasketItems(
            $this->unzerBasketItemFactoryService,
            $this->unzerBasketItemConverterService
        );
    }

    public function testGetVoucherBasketItems()
    {
        $basket = $this->createMock(Basket::class);
        $discounts = [(object) ['dDiscount' => 5.00]];
        $vouchers = [(object) ['dVoucherdiscount' => 10.00]];
        $convertedDiscounts = [5.00];
        $convertedVouchers = [10.00];
        $basketItemDiscount = $this->createMock(BasketItem::class);
        $basketItemVoucher = $this->createMock(BasketItem::class);

        $basket->method('getDiscounts')->willReturn($discounts);
        $basket->method('getVouchers')->willReturn($vouchers);

        $this->unzerBasketItemConverterService->method('convertDiscountsToVoucherAmounts')
            ->with($discounts)
            ->willReturn($convertedDiscounts);
        $this->unzerBasketItemConverterService->method('convertVouchersToVoucherAmounts')
            ->with($vouchers)
            ->willReturn($convertedVouchers);

        $this->unzerBasketItemFactoryService->method('create')->willReturnMap([
            [5.00, $basketItemDiscount],
            [10.00, $basketItemVoucher]
        ]);

        $result = $this->unzerVoucherBasketItems->getVoucherBasketItems($basket);

        $this->assertCount(2, $result);
        $this->assertSame($basketItemDiscount, $result[0]);
        $this->assertSame($basketItemVoucher, $result[1]);
    }

    public function testGetVoucherBasketItemsReturnsEmptyArrayWhenNoItems()
    {
        $basket = $this->createMock(Basket::class);
        $discounts = [];
        $vouchers = [];
        $convertedDiscounts = [];
        $convertedVouchers = [];

        $basket->method('getDiscounts')->willReturn($discounts);
        $basket->method('getVouchers')->willReturn($vouchers);

        $this->unzerBasketItemConverterService->method('convertDiscountsToVoucherAmounts')
            ->with($discounts)
            ->willReturn($convertedDiscounts);
        $this->unzerBasketItemConverterService->method('convertVouchersToVoucherAmounts')
            ->with($vouchers)
            ->willReturn($convertedVouchers);

        $result = $this->unzerVoucherBasketItems->getVoucherBasketItems($basket);

        $this->assertEquals([], $result);
    }
}
