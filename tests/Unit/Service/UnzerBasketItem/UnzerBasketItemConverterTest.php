<?php

namespace OxidSolutionCatalysts\Unzer\Tests\Unit\Service\UnzerBasketItem;

use OxidSolutionCatalysts\Unzer\Service\UnzerBasketItem\UnzerBasketItemConverter;
use PHPUnit\Framework\TestCase;

class UnzerBasketItemConverterTest extends TestCase
{
    /** @var UnzerBasketItemConverter */
    private $converter;

    protected function setUp(): void
    {
        $this->converter = new UnzerBasketItemConverter();
    }

    /**
     * @covers \OxidSolutionCatalysts\Unzer\Service\UnzerBasketItem\UnzerBasketItemConverter::convertDiscountsToVoucherAmounts
     */
    public function testConvertDiscountsToVoucherAmounts(): void
    {
        $discountItems = [
            (object)['dDiscount' => 5.00],
            (object)['dDiscount' => 10.00],
            (object)['dDiscount' => 15.00]
        ];

        $result = $this->converter->convertDiscountsToVoucherAmounts($discountItems);

        $this->assertCount(3, $result);
        $this->assertEquals(5.00, $result[0]);
        $this->assertEquals(10.00, $result[1]);
        $this->assertEquals(15.00, $result[2]);
    }

    /**
     * @covers \OxidSolutionCatalysts\Unzer\Service\UnzerBasketItem\UnzerBasketItemConverter::convertVouchersToVoucherAmounts
     */
    public function testConvertVouchersToVoucherAmounts(): void
    {
        $voucherItems = [
            (object)['dVoucherdiscount' => 20.00],
            (object)['dVoucherdiscount' => 30.00],
            (object)['dVoucherdiscount' => 40.00]
        ];

        $result = $this->converter->convertVouchersToVoucherAmounts($voucherItems);

        $this->assertCount(3, $result);
        $this->assertEquals(20.00, $result[0]);
        $this->assertEquals(30.00, $result[1]);
        $this->assertEquals(40.00, $result[2]);
    }
}
