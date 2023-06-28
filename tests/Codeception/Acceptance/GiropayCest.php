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
final class GiropayCest extends BaseCest
{
    private $giropayPaymentLabel = "//label[@for='payment_oscunzer_giropay']";
    private $banknameInput = "//input[@id='tags']";
    private $banknameA = "//ul[@class='ui-menu ui-widget ui-widget-content ui-autocomplete ui-front']";
    private $continueButton = "//input[@name='continueBtn']";
    private $accountLabel = "//input[@name='account/addition[@name=benutzerkennung]']";
    private $PINLabel = "//input[@name='ticket/pin']";
    private $payNowButton = "//input[@value='Jetzt bezahlen']";
    private $chooseTANLabel = "//input[@id='TV5']";
    private $nextStepButton = "//input[@name='weiterButton' and @type='submit']";
    private $TANLabel = "//input[@name='ticket/tan']";
    private $yesButton = "//button[@id='yes']";

    protected function _getOXID(): array
    {
        return ['oscunzer_giropay'];
    }

     /**
     * @param AcceptanceTester $I
     * @group GiropayPaymentTest
     */
    public function checkPaymentWorks(AcceptanceTester $I)
    {
        $I->wantToTest('Test Giropay payment works');
        $this->_initializeTest();
        $this->_choosePayment($this->giropayPaymentLabel);
        $this->_submitOrder();

        $giropayPaymentData = Fixtures::get('giropay_payment');

        // first page : put in bankname
        $I->waitForElement($this->banknameInput);
        $I->fillField($this->banknameInput, $giropayPaymentData['BIC']);
        $I->waitForElement($this->banknameA);
        $I->wait(5);
        $I->canSeeAndClick($this->banknameA);
        $I->click($this->continueButton);

        // accept using of cache
        $I->waitForElement($this->yesButton);
        $I->click($this->yesButton);

        // second page : login
        $I->waitForElement($this->accountLabel);
        $I->fillField($this->accountLabel, $giropayPaymentData['USER']);
        $I->fillField($this->PINLabel, $giropayPaymentData['USER_PIN']);
        $I->click($this->payNowButton);

        // third page : choose TAN options
        $I->waitForText($this->_getPrice());
        $I->waitForElement($this->chooseTANLabel);
        $I->click($this->chooseTANLabel);
        $I->click($this->nextStepButton);

        // fourth page : login TAN
        $I->waitForElement($this->TANLabel);
        $I->fillField($this->TANLabel, $giropayPaymentData['USER_TAN']);
        $I->pressKey($this->TANLabel, "\n");

        // fifth page : check information
        $I->waitForElement($this->nextStepButton);
        $I->click($this->nextStepButton);

        // sixth page : confirm payment
        $I->waitForElement($this->TANLabel);
        $I->fillField($this->TANLabel, $giropayPaymentData['USER_TAN']);
        $I->click($this->payNowButton);

        $this->_checkSuccessfulPayment();
    }
}
