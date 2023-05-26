<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

namespace OxidSolutionCatalysts\Unzer\Tests\Integration\Model;

use OxidEsales\EshopCommunity\Tests\Integration\IntegrationTestCase;
use OxidSolutionCatalysts\Unzer\Model\Payment;
use OxidEsales\EshopCommunity\Internal\Container\ContainerFactory;
use OxidEsales\EshopCommunity\Internal\Framework\Module\Configuration\Bridge\ModuleSettingBridgeInterface;
use OxidSolutionCatalysts\Unzer\Core\UnzerDefinitions;
use OxidSolutionCatalysts\Unzer\Module;

class PaymentTest extends IntegrationTestCase
{
    public function testIsUnzerPayment()
    {
        $payment = oxNew(Payment::class);
        $payment->setId('oscunzerExampleId');
        $this->assertTrue($payment->isUnzerPayment());
    }

    public function testIsNotUnzerPayment()
    {
        $payment = oxNew(Payment::class);
        $payment->setId('exampleId');
        $this->assertFalse($payment->isUnzerPayment());
    }

    public function testIsUnzerPaymentTypeNotAllowedOnNotUnzerPayment()
    {
        $payment = oxNew(Payment::class);
        $payment->setId('notUnzerExampleId');
        $this->assertFalse($payment->isUnzerPaymentTypeAllowed());
    }

    public function testIsUnzerPaymentTypeAllowedOnNotUnzerPaymentAndValidCurrency()
    {
        $di = ContainerFactory::getInstance()->getContainer();
        $bridge = $di->get(ModuleSettingBridgeInterface::class);
        $bridge->save('production-UnzerPrivateKey', 's-priv-someExampleOfGoodKey', Module::MODULE_ID);

        $payment = oxNew(Payment::class);
        $payment->load(UnzerDefinitions::SEPA_UNZER_PAYMENT_ID);

        $this->assertTrue($payment->isUnzerPaymentTypeAllowed());
    }
}
