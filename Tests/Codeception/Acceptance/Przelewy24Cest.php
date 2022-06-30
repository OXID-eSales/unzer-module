<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

declare(strict_types=1);

namespace OxidSolutionCatalysts\Unzer\Tests\Codeception\Acceptance;

use OxidEsales\Eshop\Core\Registry;
use OxidSolutionCatalysts\Unzer\Tests\Codeception\AcceptanceTester;

class Przelewy24Cest extends BaseCest
{
    private $przelewy24PaymentLabel = "//label[@for='payment_oscunzer_przelewy24']";
    private $bankLink = "//a[@data-search='mBank - mTransfer 25']";
    private $submitButton = "//button[@type='submit']";
    private $payButton = "//button[@id='pay_by_link_pay']";

    /**
     * @param AcceptanceTester $I
     * @group Przelewey24PaymentTest
     */
    public function checkPaymentWorks(AcceptanceTester $I)
    {
        $I->wantToTest('Test Giropay payment works');
        $I->updateInDatabase('oxpayments', ['OXACTIVE' => 1], ['OXID' => 'oscunzer_przelewy24']);
        $oConfig = Registry::getConfig();
        $oConfig->saveSystemConfigParameter('arr', 'aCurrencies', [0 => 'PLN@ 4.66@ ,@ @ zł@ 2']);
        $oConfig->setActShopCurrency(0);

        $this->_setAcceptance($I);
        $this->_initializeTest();


        $orderPage = $this->_choosePayment($this->przelewy24PaymentLabel);
        $orderPage->submitOrder();

        // first page : choose bank
        $I->waitForElement($this->bankLink);
        $I->click($this->bankLink);

        // second page : log in
        $I->waitForElement($this->submitButton);
        $I->click($this->submitButton);

        // third page : payment
        $I->waitForElement($this->payButton);
        $I->click($this->payButton);

        $I->waitForText($this->_getTranslator()->translate('THANK_YOU'));
    }
}
