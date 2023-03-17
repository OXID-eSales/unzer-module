<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

declare(strict_types=1);

namespace OxidSolutionCatalysts\Unzer\Tests\Integration\Service;

use OxidSolutionCatalysts\Unzer\Core\UnzerDefinitions;
use OxidSolutionCatalysts\Unzer\Service\StaticContent;
use OxidSolutionCatalysts\Unzer\Traits\ServiceContainer;
use OxidEsales\Eshop\Application\Model\Payment as EshopModelPayment;
use OxidEsales\Eshop\Application\Model\Content as EshopModelContent;
use PHPUnit\Framework\TestCase;

final class StaticContentTest extends TestCase
{
    use ServiceContainer;

    public function testCreateStaticContent()
    {
        $before = oxNew(EshopModelContent::class);
        $before->loadByIdent('oscunzersepamandatetext');
        $before->delete();

        $deleted = oxNew(EshopModelContent::class);
        $this->assertFalse($deleted->loadByIdent('oscunzersepamandatetext'));

        $service = $this->getServiceFromContainer(StaticContent::class);
        $service->ensureStaticContents();

        $after = oxNew(EshopModelContent::class);
        $after->loadByIdent('oscunzersepamandatetext');
        $after->loadInLang(0, $after->getId());
        $this->assertEquals(
            UnzerDefinitions::getUnzerStaticContents()['oscunzersepamandatetext']['oxtitle_de'],
            $after->getTitle()
        );
        $after->loadInLang(1, $after->getId());
        $this->assertEquals(
            UnzerDefinitions::getUnzerStaticContents()['oscunzersepamandatetext']['oxtitle_en'],
            $after->getTitle()
        );
    }

    public function testExistingContentIsNotChanged(): void
    {
        $before = oxNew(EshopModelContent::class);
        $before->loadByIdent('oscunzersepamandatetext');
        $before->delete();

        $deleted = oxNew(EshopModelContent::class);
        $this->assertFalse($deleted->loadByIdent('oscunzersepamandatetext'));

        $service = $this->getServiceFromContainer(StaticContent::class);
        $service->ensureStaticContents();

        $changeme = oxNew(EshopModelContent::class);
        $changeme->loadByIdent('oscunzersepamandatetext');
        $changeme->loadInLang(0, $changeme->getId());
        $changeme->assign(['oxtitle' => 'some test title']);
        $changeme->save();

        //now run service again
        $service = $this->getServiceFromContainer(StaticContent::class);
        $service->ensureStaticContents();

        $after = oxNew(EshopModelContent::class);
        $after->loadByIdent('oscunzersepamandatetext');
        $this->assertEquals('some test title', $after->getTitle());
        $after->loadInLang(1, $after->getId());
        $this->assertEquals(
            UnzerDefinitions::getUnzerStaticContents()['oscunzersepamandatetext']['oxtitle_en'],
            $after->getTitle()
        );
    }

    public function testExistingPaymentsAreNotChanged(): void
    {
        $payment = oxNew(EshopModelPayment::class);
        if (!$payment->loadInLang(0, UnzerDefinitions::SOFORT_UNZER_PAYMENT_ID)) {
            $payment->setId(UnzerDefinitions::SOFORT_UNZER_PAYMENT_ID);
            $payment->setLanguage(0);
        }
        $payment->assign(
            [
                'oxdesc' => 'test_desc_de',
                'oxlongdesc' => 'test_longdesc_de'
            ]
        );
        $payment->save();

        $service = $this->getServiceFromContainer(StaticContent::class);
        $service->ensureUnzerPaymentMethods();

        $payment = oxNew(EshopModelPayment::class);
        $payment->loadInLang(0, UnzerDefinitions::SOFORT_UNZER_PAYMENT_ID);
        $this->assertEquals('test_desc_de', $payment->getFieldData('oxdesc'));
        $this->assertEquals('test_longdesc_de', $payment->getFieldData('oxlongdesc'));

        $payment->loadInLang(1, UnzerDefinitions::SOFORT_UNZER_PAYMENT_ID);
        $this->assertEquals(
            UnzerDefinitions::getUnzerDefinitions()
                [UnzerDefinitions::SOFORT_UNZER_PAYMENT_ID]['descriptions']['en']['desc'],
            $payment->getFieldData('oxdesc')
        );
        $this->assertEquals(
            UnzerDefinitions::getUnzerDefinitions()
                [UnzerDefinitions::SOFORT_UNZER_PAYMENT_ID]['descriptions']['en']['longdesc'],
            $payment->getFieldData('oxlongdesc')
        );
    }

    public function testEnsurePaymentMethods(): void
    {
        $paymentIds = array_keys(UnzerDefinitions::getUnzerDefinitions());

        //clean up before test
        foreach ($paymentIds as $paymentId) {
            $payment = oxNew(EshopModelPayment::class);
            $payment->load($paymentId);
            $payment->delete();
        }

        $service = $this->getServiceFromContainer(StaticContent::class);
        $service->ensureUnzerPaymentMethods();

        foreach ($paymentIds as $paymentId) {
            $payment = oxNew(EshopModelPayment::class);
            $this->assertTrue($payment->load($paymentId));

            $payment->loadInLang(0, $paymentId);
            $this->assertEquals(
                UnzerDefinitions::getUnzerDefinitions()[$paymentId]['descriptions']['de']['desc'],
                $payment->getRawFieldData('oxdesc')
            );
            $this->assertEquals(
                UnzerDefinitions::getUnzerDefinitions()[$paymentId]['descriptions']['de']['longdesc'],
                $payment->getRawFieldData('oxlongdesc')
            );

            $payment->loadInLang(1, $paymentId);
            $this->assertEquals(
                UnzerDefinitions::getUnzerDefinitions()[$paymentId]['descriptions']['en']['desc'],
                $payment->getRawFieldData('oxdesc')
            );
            $this->assertEquals(
                UnzerDefinitions::getUnzerDefinitions()[$paymentId]['descriptions']['en']['longdesc'],
                $payment->getRawFieldData('oxlongdesc')
            );

            $this->assertNotEmpty($payment->getCountries(), $paymentId);
        }
    }
}
