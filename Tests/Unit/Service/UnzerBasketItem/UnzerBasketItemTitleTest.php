<?php

namespace OxidSolutionCatalysts\Unzer\Tests\Unit\Service\UnzerBasketItem;

use OxidSolutionCatalysts\Unzer\Service\UnzerBasketItem\UnzerBasketItemTitle;
use OxidSolutionCatalysts\Unzer\Service\Translator;
use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\MockObject\MockObject;

class UnzerBasketItemTitleTest extends TestCase
{
    /** @var MockObject|Translator */
    private $translator;

    /** @var UnzerBasketItemTitle */
    private $unzerBasketItemTitle;

    protected function setUp(): void
    {
        $this->translator = $this->createMock(Translator::class);

        $this->unzerBasketItemTitle = new UnzerBasketItemTitle($this->translator);
    }

    /**
     * @covers \OxidSolutionCatalysts\Unzer\Service\UnzerBasketItem\UnzerBasketItemTitle::getTitle
     */
    public function testGetTitleReturnsDiscountForPositiveAmount()
    {
        $amount = 10.00;

        $this->translator->method('translate')->with('DISCOUNT')->willReturn('DISCOUNT');

        $result = $this->unzerBasketItemTitle->getTitle($amount);

        $this->assertEquals('DISCOUNT', $result);
    }

    /**
     * @covers \OxidSolutionCatalysts\Unzer\Service\UnzerBasketItem\UnzerBasketItemTitle::getTitle
     */
    public function testGetTitleReturnsSurchargeForNegativeAmount()
    {
        $amount = -10.00;

        $this->translator->method('translate')->with('SURCHARGE')->willReturn('SURCHARGE');

        $result = $this->unzerBasketItemTitle->getTitle($amount);

        $this->assertEquals('SURCHARGE', $result);
    }
}
