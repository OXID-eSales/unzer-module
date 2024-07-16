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
    private mixed $fixtures;

    protected function _getOXID(): array
    {
        return ['oscunzer_card'];
    }

    /**
     * @param AcceptanceTester $I
     * @return void
     */
    private function _prepareCreditCardTest(AcceptanceTester $I)
    {
        $this->_initializeTest();
    }

    /**
     * @param string $name Fixtures name
     * @return void
     */
    private function _submitCreditCardPayment(string $name)
    {
        $orderPage = $this->_choosePayment($this->cardPaymentLabel);

        $this->I->waitForPageLoad();
        $this->finishCardSubmit($name);

        $orderPage->submitOrder();
    }

    /**
     * @return void
     */
    private function _checkCreditCardPayment()
    {
        $this->I->waitForText($this->toCompleteAuthentication, 60);
        $this->I->click($this->toCompleteAuthentication);

        $this->_checkSuccessfulPayment();
    }

    /**
     * @param $stock int is oxarticles->OXSTOCK
     * @param $flag int is oxarticles->OXSTOCKFLAG
     * @return void
     */
    private function _updateArticleStockAndFlag($stock, $flag)
    {
        $article = Fixtures::get('product');
        $this->I->updateInDatabase(
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
        $this->_updateArticleStockAndFlag(15, 1);
        $this->_prepareCreditCardTest($I);

        $this->_submitCreditCardPayment('mastercard_payment');
        $this->_checkCreditCardPayment();
    }

    /**
     * @group CreditCardPaymentTest1
     */
    public function checkPaymentUsingMastercardWithLastStockItemWorks(AcceptanceTester $I)
    {
        $I->wantToTest('Test Credit Card payment using Mastercard with last stock item works');
        $this->_updateArticleStockAndFlag(1, 3);
        $this->_prepareCreditCardTest($I);

        $this->_submitCreditCardPayment('mastercard_payment');
        $this->_checkCreditCardPayment();
    }

    /**
     * @param AcceptanceTester $I
     * @group CreditCardPaymentTest
     */
    public function checkPaymentUsingVisaWorks(AcceptanceTester $I)
    {
        $I->wantToTest('Test Credit Card payment using Visa works');
        $this->_updateArticleStockAndFlag(15, 1);
        $this->_prepareCreditCardTest($I);

        $this->_submitCreditCardPayment('visa_payment');
        $this->_checkCreditCardPayment();
    }

    /**
     * @param AcceptanceTester $I
     * @return void
     * @group CreditCardPaymentTest1
     */
    public function checkPaymentUsingVisaWithLastStockItemWorks(AcceptanceTester $I)
    {
        $I->wantToTest('Test Credit Card payment using Visa with last stock item works');
        $this->_updateArticleStockAndFlag(1, 3);
        $this->_prepareCreditCardTest($I);

        $this->_submitCreditCardPayment('visa_payment');
        $this->_checkCreditCardPayment();
    }

    /**
     * @param AcceptanceTester $I
     * @group CreditCardPaymentTest
     */
    public function checkPaymentUsingMaestroWorks(AcceptanceTester $I)
    {
        $I->wantToTest('Test Credit Card payment using Maestro works');
    }

    /**
     * @throws \Exception
     */
    private function _submitCreditCardPaymentAndSavePayment(string $string)
    {
        $orderPage = $this->_choosePayment($this->cardPaymentLabel);

        $this->I->waitForPageLoad();
        $this->I->waitForElementClickable($this->confirmSavePayment);
        $this->I->click($this->confirmSavePayment);
        $this->finishCardSubmit($string);

        $orderPage->submitOrder();
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
}
