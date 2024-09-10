<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

declare(strict_types=1);

namespace OxidSolutionCatalysts\Unzer\Tests\Codeception\Acceptance;

use Codeception\Util\Fixtures;
use OxidSolutionCatalysts\Unzer\Tests\Codeception\AcceptanceTester;

/**
 * @group unzer_module
 * @group ThirdGroup
 * @group CreditCardCest
 */
final class CreditCardCest extends AbstractCreditCardCest
{
    /**
     * @group CreditCardPaymentTest1
     */
    public function testPaymentUsingMastercardWorks(AcceptanceTester $I): void
    {
        $I->wantToTest('Test Credit Card payment using Mastercard works');
        $this->updateArticleStockAndFlag(15, 1);
        $this->initializeTest();
        $this->submitCreditCardPayment('mastercard_payment');
        $this->checkCreditCardPayment();
    }

    /**
     * @group CreditCardPaymentTest
     */
    public function testPaymentUsingMastercardWithLastStockItemWorks(AcceptanceTester $I): void
    {
        $I->wantToTest('Credit Card payment using Mastercard with last stock item works');
        $this->updateArticleStockAndFlag(1, 3);
        $this->initializeTest();
        $this->submitCreditCardPayment('mastercard_payment');
        $this->checkCreditCardPayment();
    }

    /**
     * @group CreditCardPaymentTest
     */
    public function testPaymentUsingVisaWorks(AcceptanceTester $I): void
    {
        $I->wantToTest('Credit Card payment using Visa works');
        $this->updateArticleStockAndFlag(15, 1);
        $this->initializeTest();
        $this->submitCreditCardPayment('visa_payment');
        $this->checkCreditCardPayment();
    }

    /**
     * @group CreditCardPaymentTest
     */
    public function testPaymentUsingVisaWithLastStockItemWorks(AcceptanceTester $I): void
    {
        $I->wantToTest('Credit Card payment using Visa with last stock item works');
        $this->updateArticleStockAndFlag(1, 3);
        $this->initializeTest();
        $this->submitCreditCardPayment('visa_payment');
        $this->checkCreditCardPayment();
    }
}
