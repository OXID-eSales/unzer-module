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
    private string $confirmSavePayment = "#oscunzersavepayment";
    private string $useSavedCardForPayment = '//*[@id="payment-saved-cards"]/table/tbody/tr/td[3]/input';

    /**
     * @group CreditCardPaymentTest
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

    /**
     * @throws \Exception
     */
    private function finishCardSubmit(string $name): void
    {
        $fixtures = Fixtures::get($name);
        $this->I->waitForElement($this->cardNumberIframe);
        $this->I->switchToIFrame($this->cardNumberIframe);
        $this->I->wait(5);
        $this->I->fillField($this->cardNumberInput, $fixtures['cardnumber']);
        $this->I->switchToNextTab(1);
        $this->I->switchToIFrame($this->expireDateIframe);
        $this->I->fillField($this->expireDateInput, '12/' . date('y'));
        $this->I->switchToNextTab(1);
        $this->I->switchToIFrame($this->CVCIframe);
        $this->I->fillField($this->CVCInput, $fixtures['CVC']);
        $this->I->switchToFrame(null);
    }

    protected function getOXID(): array
    {
        return ['oscunzer_card'];
    }

    private function submitCreditCardPayment(string $name): void
    {
        $orderPage = $this->choosePayment($this->cardPaymentLabel);
        $this->I->waitForPageLoad();
        $this->finishCardSubmit($name);
        $orderPage->submitOrder();
    }

    private function useSavedCardToPay(): void
    {
        $orderPage = $this->choosePayment($this->cardPaymentLabel);

        $this->I->waitForPageLoad();
        $this->I->click($this->useSavedCardForPayment);

        $orderPage->submitOrder();
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
}
