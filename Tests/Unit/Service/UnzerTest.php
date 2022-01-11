<?php

namespace OxidSolutionCatalysts\Unzer\Tests\Unit\Service;

use OxidEsales\Eshop\Application\Model\Basket as ShopBasketModel;
use OxidEsales\Eshop\Application\Model\BasketItem;
use OxidEsales\Eshop\Core\Config;
use OxidEsales\Eshop\Core\Language;
use OxidEsales\Eshop\Core\Registry;
use OxidEsales\Eshop\Core\Request;
use OxidEsales\Eshop\Core\Session;
use OxidSolutionCatalysts\Unzer\Service\Context;
use OxidSolutionCatalysts\Unzer\Service\ModuleSettings;
use OxidSolutionCatalysts\Unzer\Service\Translator;
use OxidSolutionCatalysts\Unzer\Service\Unzer;
use PHPUnit\Framework\TestCase;

class UnzerTest extends TestCase
{

    /**
     * @dataProvider prepareRedirectUrlDataProvider
     */
    public function testprepareRedirectUrl($shopUrl, $destination, $expectedShopUrl)
    {
        $sut = $this->getSut();

        Registry::set(Config::class, $this->createConfiguredMock(Config::class, ['getSslShopUrl' => $shopUrl]));

        $this->assertSame(
            $expectedShopUrl,
            $sut->prepareRedirectUrl($destination)
        );
    }

    public function prepareRedirectUrlDataProvider(): array
    {
        return [
            ['value/', 'order', 'value/index.php?cl=order'],
            ['value/', 'thankyou', 'value/index.php?cl=thankyou']
        ];
    }

    /**
     * @dataProvider prepareOrderRedirectUrlDataProvider
     */
    public function testprepareOrderRedirectUrl($shopUrl, $addPending, $expectedShopUrl)
    {
        $sut = $this->getSut();

        Registry::set(Config::class, $this->createConfiguredMock(Config::class, ['getSslShopUrl' => $shopUrl]));

        $this->assertSame(
            $expectedShopUrl,
            $sut->prepareOrderRedirectUrl($addPending)
        );
    }

    public function prepareOrderRedirectUrlDataProvider(): array
    {
        return [
            ['value/', false, 'value/index.php?cl=order'],
            ['value/', true, 'value/index.php?cl=order&fnc=unzerExecuteAfterRedirect&uzrredirect=1']
        ];
    }

    /**
     * @dataProvider getPaymentProcedureDataProvider
     */
    public function testGetPaymentProcedure($paymentId, $expectedProcedure)
    {
        $sut = $this->getSut([
            ModuleSettings::class => $this->createConfiguredMock(ModuleSettings::class, [
                'getPaymentProcedureSetting' => 'special'
            ])
        ]);

        $this->assertSame($expectedProcedure, $sut->getPaymentProcedure($paymentId));
    }

    public function getPaymentProcedureDataProvider(): array
    {
        return [
            ['paypal', 'special'],
            ['card', 'special'],
            ['other', ModuleSettings::PAYMENT_CHARGE],
        ];
    }

    private function getSut(array $settings = []): Unzer
    {
        return new Unzer(
            $this->createPartialMock(Session::class, []),
            $this->createPartialMock(Translator::class, []),
            $this->createPartialMock(Context::class, []),
            $settings[ModuleSettings::class] ?:
                $this->createPartialMock(ModuleSettings::class, []),
            $settings[Request::class] ?:
                $this->createPartialMock(Request::class, []),
        );
    }

    public function testGetBasicUnzerBasket(): void
    {
        $currency = new \stdClass();
        $currency->name = 'EUR';

        $shopBasketModel = $this->createConfiguredMock(ShopBasketModel::class, [
            'getNettoSum' => 123.45,
            'getBasketCurrency' => $currency
        ]);

        $sut = $this->getSut();
        $result = $sut->getUnzerBasket('someOrderId', $shopBasketModel);

        $this->assertInstanceOf(\UnzerSDK\Resources\Basket::class, $result);
        $this->assertSame(123.45, $result->getAmountTotalGross());
        $this->assertSame('EUR', $result->getCurrencyCode());
        $this->assertSame('someOrderId', $result->getOrderId());
    }

    public function testGetContentUnzerBasket(): void
    {
        $currency = new \stdClass();
        $currency->name = 'EUR';

        $basketItem1 = $this->createConfiguredMock(BasketItem::class, [
            'getTitle' => 'basket item title 1',
            'getPrice' => new \OxidEsales\Eshop\Core\Price(100),
            'getUnitPrice' => new \OxidEsales\Eshop\Core\Price(20),
            'getAmount' => 5
        ]);

        $basketItem2 = $this->createConfiguredMock(BasketItem::class, [
            'getTitle' => 'basket item title 2',
            'getPrice' => new \OxidEsales\Eshop\Core\Price(40),
            'getUnitPrice' => new \OxidEsales\Eshop\Core\Price(10),
            'getAmount' => 4
        ]);

        $shopBasketModel = $this->createConfiguredMock(ShopBasketModel::class, [
            'getNettoSum' => 123.45,
            'getBasketCurrency' => $currency,
            'getContents' => [$basketItem1, $basketItem2],
        ]);

        $sut = $this->getSut();
        $result = $sut->getUnzerBasket("someOrderId", $shopBasketModel);

        $this->assertSame(2, $result->getItemCount());

        /** @var \UnzerSDK\Resources\EmbeddedResources\BasketItem[] $items */
        $items = $result->getBasketItems();

        $this->assertSame('basket item title 1', $items[0]->getTitle());
        $this->assertSame(20.0, $items[0]->getAmountPerUnit());
        $this->assertSame(100.0, $items[0]->getAmountNet());
        $this->assertSame(5, $items[0]->getQuantity());
    }

    public function testGetUnzerPaymentIdFromRequest(): void
    {
        $requestStub = $this->createPartialMock(Request::class, ['getRequestParameter']);
        $requestStub->method('getRequestParameter')->with('paymentData')->willReturn(
            json_encode(['id' => 'someId'])
        );

        $sut = $this->getSut([
            Request::class => $requestStub
        ]);

        $this->assertSame('someId', $sut->getUnzerPaymentIdFromRequest());
    }

    public function testGetUnzerPaymentIdFromRequestFailure(): void
    {
        $sut = $this->getSut();

        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('oscunzer_WRONGPAYMENTID');

        $sut->getUnzerPaymentIdFromRequest();
    }
}
