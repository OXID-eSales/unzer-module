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
    private $cardPaymentLabel = "//label[@for='payment_oscunzer_card']";
    private $cardNumberIframe = "//iframe[contains(@id, 'unzer-number-iframe')]";
    private $expireDateIframe = "//iframe[contains(@id, 'unzer-expiry-iframe')]";
    private $CVCIframe = "//iframe[contains(@id, 'unzer-cvc-iframe')]";
    private $cardNumberInput = "//input[@id='card-number']";
    private $expireDateInput = "//input[@id='card-expiry-date']";
    private $CVCInput = "//input[@id='card-ccv']";
    private $toCompleteAuthentication = "Click here to complete authentication.";

    protected function getOXID(): array
    {
        return ['oscunzer_card'];
    }

    /**
     * @param AcceptanceTester $I
     * @return void
     */
    private function prepareCreditCardTest(AcceptanceTester $I)
    {
        $this->initializeTest();
    }

    /**
     * @param string $name Fixtures name
     * @return void
     */
    private function submitCreditCardPayment(string $name)
    {
        $this->choosePayment($this->cardPaymentLabel);

        $fixtures = Fixtures::get($name);
        $this->getAcceptance()->waitForPageLoad();
        $this->getAcceptance()->waitForElement($this->cardNumberIframe);
        $this->getAcceptance()->switchToIFrame($this->cardNumberIframe);
        $this->getAcceptance()->fillField($this->cardNumberInput, $fixtures['cardnumber']);
        $this->getAcceptance()->switchToNextTab(1);
        $this->getAcceptance()->switchToIFrame($this->expireDateIframe);
        $this->getAcceptance()->fillField($this->expireDateInput, '12/' . date('y'));
        $this->getAcceptance()->switchToNextTab(1);
        $this->getAcceptance()->switchToIFrame($this->CVCIframe);
        $this->getAcceptance()->fillField($this->CVCInput, $fixtures['CVC']);
        $this->getAcceptance()->switchToFrame(null);

        $this->submitOrder();
    }

    /**
     * @return void
     */
    private function checkCreditCardPayment()
    {
        $this->getAcceptance()->waitForText($this->toCompleteAuthentication, 60);
        $this->getAcceptance()->click($this->toCompleteAuthentication);

        $this->checkSuccessfulPayment();
    }

    /**
     * @param $stock int is oxarticles->OXSTOCK
     * @param $flag int is oxarticles->OXSTOCKFLAG
     * @return void
     */
    private function updateArticleStockAndFlag($stock, $flag)
    {
        $article = Fixtures::get('product');
        $this->getAcceptance()->updateInDatabase(
            'oxarticles',
            ['OXSTOCK' => $stock, 'OXSTOCKFLAG' => $flag],
            ['OXID' => $article['id']]
        );
    }

    /**
     * @param AcceptanceTester $I
     * @group CreditCardPaymentTest1
     */
    public function checkPaymentUsingMastercardWorks(AcceptanceTester $I)
    {
        $I->wantToTest('Test Credit Card payment using Mastercard works');
        $this->updateArticleStockAndFlag(15, 1);
        $this->prepareCreditCardTest($I);

        $this->submitCreditCardPayment('mastercard_payment');
        $this->checkCreditCardPayment();
    }

    /**
     * @param AcceptanceTester $I
     * @return void
     * @group CreditCardPaymentTest
     */
    public function checkPaymentUsingMastercardWithLastStockItemWorks(AcceptanceTester $I)
    {
        $I->wantToTest('Test Credit Card payment using Mastercard with last stock item works');
        $this->updateArticleStockAndFlag(1, 3);
        $this->prepareCreditCardTest($I);

        $this->submitCreditCardPayment('mastercard_payment');
        $this->checkCreditCardPayment();
    }

    /**
     * @param AcceptanceTester $I
     * @group CreditCardPaymentTest
     */
    public function checkPaymentUsingVisaWorks(AcceptanceTester $I)
    {
        $I->wantToTest('Test Credit Card payment using Visa works');
        $this->updateArticleStockAndFlag(15, 1);
        $this->prepareCreditCardTest($I);

        $this->submitCreditCardPayment('visa_payment');
        $this->checkCreditCardPayment();
    }

    /**
     * @param AcceptanceTester $I
     * @return void
     * @group CreditCardPaymentTest
     */
    public function checkPaymentUsingVisaWithLastStockItemWorks(AcceptanceTester $I)
    {
        $I->wantToTest('Test Credit Card payment using Visa with last stock item works');
        $this->updateArticleStockAndFlag(1, 3);
        $this->prepareCreditCardTest($I);

        $this->submitCreditCardPayment('visa_payment');
        $this->checkCreditCardPayment();
    }

    /**
     * @param AcceptanceTester $I
     * @group CreditCardPaymentTest
     */
    public function checkPaymentUsingMaestroWorks(AcceptanceTester $I)
    {
        $I->wantToTest('Test Credit Card payment using Maestro works');
    }
}
