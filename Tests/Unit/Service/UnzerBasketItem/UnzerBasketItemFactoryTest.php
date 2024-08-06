<?php

namespace OxidSolutionCatalysts\Unzer\Tests\Unit\Service\UnzerBasketItem;

use OxidSolutionCatalysts\Unzer\Service\UnzerBasketItem\UnzerBasketItemFactory;
use OxidSolutionCatalysts\Unzer\Service\UnzerBasketItem\UnzerBasketItemTitle;
use UnzerSDK\Constants\BasketItemTypes;
use UnzerSDK\Resources\EmbeddedResources\BasketItem;
use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\MockObject\MockObject;

class UnzerBasketItemFactoryTest extends TestCase
{
    /** @var MockObject|UnzerBasketItemTitle */
    private $unzerBasketItemTitleService;

    /** @var UnzerBasketItemFactory */
    private $unzerBasketItemFactory;

    protected function setUp(): void
    {
        $this->unzerBasketItemTitleService = $this->createMock(UnzerBasketItemTitle::class);

        $this->unzerBasketItemFactory = new UnzerBasketItemFactory($this->unzerBasketItemTitleService);
    }

    /**
     * @covers \OxidSolutionCatalysts\Unzer\Service\UnzerBasketItem\UnzerBasketItemFactory::create
     */
    public function testCreate()
    {
        $voucherItemAmount = 25.00;

        $this->unzerBasketItemTitleService->method('getTitle')->with($voucherItemAmount)->willReturn('DISCOUNT');

        $result = $this->unzerBasketItemFactory->create($voucherItemAmount);

        $this->assertInstanceOf(BasketItem::class, $result);
        $this->assertEquals('DISCOUNT', $result->getTitle());
        $this->assertEquals(1, $result->getQuantity());
        $this->assertEquals(BasketItemTypes::VOUCHER, $result->getType());
        $this->assertEquals(0, $result->getVat());
        $this->assertEquals(0.0, $result->getAmountPerUnitGross());
        $this->assertEquals($voucherItemAmount, $result->getAmountDiscountPerUnitGross());
    }
}
