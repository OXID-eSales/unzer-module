<?php

namespace OxidSolutionCatalysts\Unzer\Tests\Integration\Controller;

use OxidEsales\Eshop\Application\Model\Payment as ShopPaymentModel;
use OxidEsales\TestingLibrary\UnitTestCase;

class PaymentTest extends UnitTestCase
{
    public function testIsUnzerPayment()
    {
        $payment = oxNew(ShopPaymentModel::class);
        $payment->setId('oscunzerExampleId');
        $this->assertTrue($payment->isUnzerPayment());
    }

    public function testIsNotUnzerPayment()
    {
        $payment = oxNew(ShopPaymentModel::class);
        $payment->setId('exampleId');
        $this->assertFalse($payment->isUnzerPayment());
    }
}
