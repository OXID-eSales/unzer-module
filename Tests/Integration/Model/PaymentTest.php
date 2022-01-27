<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

namespace OxidSolutionCatalysts\Unzer\Tests\Integration\Controller;

use OxidEsales\Eshop\Application\Model\Payment as ShopPaymentModel;
use OxidEsales\EshopCommunity\Internal\Container\ContainerFactory;
use OxidEsales\EshopCommunity\Internal\Framework\Module\Configuration\Bridge\ModuleSettingBridgeInterface;
use OxidEsales\TestingLibrary\UnitTestCase;
use OxidSolutionCatalysts\Unzer\Module;

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

    public function testIsUnzerPaymentTypeNotAllowedOnNotUnzerPayment()
    {
        $payment = oxNew(ShopPaymentModel::class);
        $payment->setId('notUnzerExampleId');
        $this->assertFalse($payment->isUnzerPaymentTypeAllowed());
    }

    public function testIsUnzerPaymentTypeAllowedOnNotUnzerPaymentAndValidCurrency()
    {
        $di = ContainerFactory::getInstance()->getContainer();
        $bridge = $di->get(ModuleSettingBridgeInterface::class);
        $bridge->save('production-UnzerPrivateKey', 's-priv-someExampleOfGoodKey', Module::MODULE_ID);

        $payment = oxNew(ShopPaymentModel::class);
        $payment->load('oscunzer_sepa');

        $this->assertTrue($payment->isUnzerPaymentTypeAllowed());
    }
}
