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
 * @group CreditCardCest
 * @group ThirdGroup
 */
final class CreditCardCest extends BaseCest
{
    private string $cardPaymentLabel = "//label[@for='payment_oscunzer_card']";
    private string $cardNumberIframe = "//iframe[contains(@id, 'unzer-number-iframe')]";
    private string $expireDateIframe = "//iframe[contains(@id, 'unzer-expiry-iframe')]";
    private string $CVCIframe = "//iframe[contains(@id, 'unzer-cvc-iframe')]";
    private string $cardNumberInput = "//input[@id='card-number']";
    private string $expireDateInput = "//input[@id='card-expiry-date']";
    private string $CVCInput = "//input[@id='card-ccv']";
    private string $toCompleteAuthentication = "Click here to complete authentication.";
    private string $cardHolderInput = "//input[@id='card-holder']";
    private string $cardHolderIframeInput = "//iframe[contains(@id, 'unzer-holder-iframe')]";

    protected function getOXID(): array
    {
        return ['oscunzer_card'];
    }

    private function submitCreditCardPayment(string $name): void
    {
        $this->choosePayment($this->cardPaymentLabel);

        $fixtures = Fixtures::get($name);
        $this->I->waitForPageLoad();
        $this->I->waitForElement($this->cardNumberIframe);
        $this->I->switchToIFrame($this->cardNumberIframe);
        $this->I->wait(5);
        $this->I->fillField($this->cardNumberInput, $fixtures['cardnumber']);
        $this->I->switchToNextTab(1);
        $this->I->switchToIFrame($this->cardHolderIframeInput);
        $this->I->fillField($this->cardHolderInput, $fixtures['cardholder']);
        $this->I->switchToNextTab(1);
        $this->I->switchToIFrame($this->expireDateIframe);
        $this->I->fillField($this->expireDateInput, '12/' . date('y'));
        $this->I->switchToNextTab(1);
        $this->I->switchToIFrame($this->CVCIframe);
        $this->I->fillField($this->CVCInput, $fixtures['CVC']);
        $this->I->switchToFrame(null);

        $this->submitOrder();
    }

    private function checkCreditCardPayment(): void
    {
        $this->I->waitForText($this->toCompleteAuthentication, 60);
        $this->I->click($this->toCompleteAuthentication);

        $this->checkSuccessfulPayment();
    }

    private function updateArticleStockAndFlag(int $stock, int $flag): void
    {
        $article = Fixtures::get('product');
        $this->I->updateInDatabase(
            'oxarticles',
            ['OXSTOCK' => $stock, 'OXSTOCKFLAG' => $flag],
            ['OXID' => $article['id']]
        );
    }

    /**
     * @group CreditCardPaymentTest1
     */
    public function checkPaymentUsingMastercardWorks(AcceptanceTester $I): void
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
    public function checkPaymentUsingMastercardWithLastStockItemWorks(AcceptanceTester $I): void
    {
        $I->wantToTest('Test Credit Card payment using Mastercard with last stock item works');
        $this->updateArticleStockAndFlag(1, 3);
        $this->initializeTest();

        $this->submitCreditCardPayment('mastercard_payment');
        $this->checkCreditCardPayment();
    }

    /**
     * @param AcceptanceTester $I
     * @group CreditCardPaymentTest
     */
    public function checkPaymentUsingVisaWorks(AcceptanceTester $I): void
    {
        $I->wantToTest('Test Credit Card payment using Visa works');
        $this->initializeTest();

        $this->submitCreditCardPayment('visa_payment');
        $this->checkCreditCardPayment();
    }

    /**
     * @group CreditCardPaymentTest
     */
    public function checkPaymentUsingVisaWithLastStockItemWorks(AcceptanceTester $I): void
    {
        $I->wantToTest('Test Credit Card payment using Visa with last stock item works');
        $this->updateArticleStockAndFlag(1, 3);
        $this->initializeTest();

        $this->submitCreditCardPayment('visa_payment');
        $this->checkCreditCardPayment();
    }
}
