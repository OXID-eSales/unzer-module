<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

namespace OxidSolutionCatalysts\Unzer\Tests\Integration\Model;

use OxidEsales\EshopCommunity\Internal\Container\ContainerFactory;
use OxidEsales\EshopCommunity\Tests\Integration\IntegrationTestCase;
use OxidSolutionCatalysts\Unzer\Exception\UnzerException;
use OxidSolutionCatalysts\Unzer\Model\Payment;
use OxidSolutionCatalysts\Unzer\Service\ModuleSettings;
use OxidSolutionCatalysts\Unzer\Core\UnzerDefinitions;

class PaymentTest extends IntegrationTestCase
{
    private ModuleSettings $moduleSettings;

    public function setUp(): void
    {
        parent::setUp();
        $container = ContainerFactory::getInstance()->getContainer();
        $this->moduleSettings = $container->get(ModuleSettings::class);
    }

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

    public function testIsUnzerPaymentTypeAllowedOnUnzerPaymentAndValidCurrency()
    {
        // Set the private key for the current system mode
        $this->moduleSettings->setSystemMode(ModuleSettings::SYSTEM_MODE_PRODUCTION);
        $this->moduleSettings->saveSetting('production-UnzerPrivateKey', 's-priv-someExampleOfGoodKey');

        $payment = oxNew(Payment::class);
        $payment->load(UnzerDefinitions::SEPA_UNZER_PAYMENT_ID);
        $payment->setId(UnzerDefinitions::SEPA_UNZER_PAYMENT_ID);

        $this->assertTrue($payment->isUnzerPaymentTypeAllowed());
    }

    public function testIsUnzerPaymentTypeAllowedWithInvalidPrivateKey()
    {
        $this->moduleSettings->setSystemMode(ModuleSettings::SYSTEM_MODE_PRODUCTION);
        $this->moduleSettings->saveSetting('production-UnzerPrivateKey', '');
        $payment = oxNew(Payment::class);
        $payment->setId(UnzerDefinitions::SEPA_UNZER_PAYMENT_ID);
        $this->expectException(UnzerException::class);
        $payment->isUnzerPaymentTypeAllowed();
    }

    public function testIsUnzerPaymentTypeAllowedInSandboxMode()
    {
        $this->moduleSettings->setSystemMode(ModuleSettings::SYSTEM_MODE_SANDBOX);
        $this->moduleSettings->saveSetting('sandbox-UnzerPrivateKey', 's-priv-someExampleOfGoodKey');

        $payment = oxNew(Payment::class);
        $payment->setId(UnzerDefinitions::SEPA_UNZER_PAYMENT_ID);

        $this->assertTrue($payment->isUnzerPaymentTypeAllowed());
    }
}
