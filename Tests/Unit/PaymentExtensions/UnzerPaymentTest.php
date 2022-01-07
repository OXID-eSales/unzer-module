<?php

namespace OxidSolutionCatalysts\Unzer\Tests\Unit\Exception;

use Exception;
use OxidEsales\Eshop\Application\Model\Basket as BasketModel;
use OxidEsales\Eshop\Application\Model\User as UserModel;
use OxidSolutionCatalysts\Unzer\PaymentExtensions\UnzerPayment;
use OxidSolutionCatalysts\Unzer\Service\Unzer as UnzerService;
use PHPUnit\Framework\TestCase;
use UnzerSDK\Resources\Basket;
use UnzerSDK\Resources\Customer;
use UnzerSDK\Resources\Metadata;
use UnzerSDK\Resources\TransactionTypes\Charge;
use UnzerSDK\Traits\CanDirectCharge;
use UnzerSDK\Unzer as UnzerSDK;

class UnzerPaymentTest extends TestCase
{
    public function testDefaultExecute(): void
    {
        $currency = new \stdClass();
        $currency->name = 'EUR';

        $userModel = $this->createPartialMock(UserModel::class, []);
        $basketModel = $this->createConfiguredMock(BasketModel::class, [
            'getPrice' => new \OxidEsales\Eshop\Core\Price(123),
            'getBasketCurrency' => $currency
        ]);
        $unzerBasket = $this->createPartialMock(Basket::class, []);

        $unzerServiceMock = $this->createPartialMock(UnzerService::class, [
            'getPaymentProcedure',
            'prepareRedirectUrl',
            'getUnzerCustomer',
            'getShopMetadata',
            'setSessionVars',
            'generateUnzerOrderId',
            'getUnzerBasket'
        ]);

        $unzerServiceMock->method('getPaymentProcedure')->willReturn('charge');
        $unzerServiceMock->method('prepareRedirectUrl')->willReturn('someRedirectUrl');
        $unzerServiceMock->method('getUnzerCustomer')->with($userModel)->willReturn($customer = new Customer());
        $unzerServiceMock->method('getShopMetadata')->willReturn($metadata = new Metadata());
        $unzerServiceMock->method('generateUnzerOrderId')->willReturn('unzerOrderId');
        $unzerServiceMock->method('getUnzerBasket')->willReturn($unzerBasket);

        $chargeResult = $this->createPartialMock(Charge::class, []);
        $unzerServiceMock->expects($this->once())->method('setSessionVars')->with($chargeResult);

        $sut = $this->getMockForAbstractClass(UnzerPayment::class, [
            $this->createPartialMock(UnzerSDK::class, []),
            $unzerServiceMock
        ], '', true, true, true, ['getUnzerPaymentTypeObject']);
        $sut->method('getUnzerPaymentTypeObject')->willReturn(
            $paymentTypeMock = $this->getMockForTrait(
                CanDirectCharge::class,
                [],
                '',
                true,
                true,
                true,
                ['charge']
            )
        );
        $paymentTypeMock->expects($this->atLeastOnce())
            ->method('charge')
            ->with(
                123,
                'EUR',
                'someRedirectUrl',
                $customer,
                'unzerOrderId',
                $metadata,
                $unzerBasket
            )
            ->willReturn($chargeResult);

        $sut->execute($userModel, $basketModel);
    }
}
