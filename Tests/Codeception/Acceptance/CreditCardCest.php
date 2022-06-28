<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

declare(strict_types=1);

namespace OxidSolutionCatalysts\Unzer\Tests\Codeception\Acceptance;

use Codeception\Util\Fixtures;
use OxidSolutionCatalysts\Unzer\Tests\Codeception\AcceptanceTester;

class CreditCardCest extends BaseCest
{
    private $cardPaymentLabel = "//label[@for='payment_oscunzer_card']";
    private $cardNumberIframe = "//iframe[contains(@id, 'unzer-number-iframe')]";
    private $expireDateIframe = "//iframe[contains(@id, 'unzer-expiry-iframe')]";
    private $CVCIframe = "//iframe[contains(@id, 'unzer-cvc-iframe')]";
    private $cardNumberInput = "//input[@id='card-number']";
    private $expireDateInput = "//input[@id='card-expiry-date']";
    private $CVCInput = "//input[@id='card-ccv']";
    private $toCompleteAuthentication = "Click here to complete authentication.";

    /**
     * @param AcceptanceTester $I
     * @return void
     */
    private function _prepareCreditCardTest(AcceptanceTester $I)
    {
        $I->updateInDatabase('oxpayments', ['OXACTIVE' => 1], ['OXID' => 'oscunzer_card']);
        $this->_setAcceptance($I);
        $this->_initializeTest();
    }

    /**
     * @param Fixtures $fixtures
     * @return void
     */
    private function _submitCreditCardPatment(string $name)
    {
        $orderPage = $this->_choosePayment($this->cardPaymentLabel);

        $fixtures = Fixtures::get($name);
        $this->_getAcceptance()->waitForElement($this->cardNumberIframe);
        $this->_getAcceptance()->switchToIFrame($this->cardNumberIframe);
        $this->_getAcceptance()->fillField($this->cardNumberInput, $fixtures['cardnumber']);
        $this->_getAcceptance()->switchToNextTab(1);
        $this->_getAcceptance()->switchToIFrame($this->expireDateIframe);
        $this->_getAcceptance()->fillField($this->expireDateInput, '12/' . date('y'));
        $this->_getAcceptance()->switchToNextTab(1);
        $this->_getAcceptance()->switchToIFrame($this->CVCIframe);
        $this->_getAcceptance()->fillField($this->CVCInput, $fixtures['CVC']);
        $this->_getAcceptance()->switchToWindow();

        $orderPage->submitOrder();
    }

    /**
     * @return void
     * @throws \Exception
     */
    private function _checkCreditCardPayment()
    {
        $this->_getAcceptance()->waitForText($this->toCompleteAuthentication, 30);
        $this->_getAcceptance()->click($this->toCompleteAuthentication);

        $this->_getAcceptance()->waitForText($this->_getTranslator()->translate('THANK_YOU'));
    }

    /**
     * @param AcceptanceTester $I
     * @group CreditCardPaymentTest
     */
    public function checkPaymentUsingMastercardWorks(AcceptanceTester $I)
    {
        $I->wantToTest('Test Credit Card payment using Mastercard works');
        $this->_prepareCreditCardTest($I);

        $this->_submitCreditCardPatment('mastercard_payment');
        $this->_checkCreditCardPayment();
    }

    /**
     * @param AcceptanceTester $I
     * @group CreditCardPaymentTest
     */
    public function checkPaymentUsingVisaWorks(AcceptanceTester $I)
    {
        $I->wantToTest('Test Credit Card payment using Visa works');
        $this->_prepareCreditCardTest($I);

        $this->_submitCreditCardPatment('visa_payment');
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
}
