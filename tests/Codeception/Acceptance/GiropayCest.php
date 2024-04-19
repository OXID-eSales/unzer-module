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
    private $banknameInput = "//input[@name='bic']";
    private $banknameA = "//ul[@class='ui-menu ui-widget ui-widget-content ui-autocomplete ui-front']";
    private $continueButton = "//button[@class='btn btn-primary']";
    private $accountLabel = "//input[@name='userLogin']";
    private $PINLabel = "//input[@name='userPIN']";
    private $payNowButton = "//input[@value='Jetzt bezahlen']";
    private $chooseTANLabel = "//input[@id='TV5']";
    private $nextStepButton = "//input[@name='weiterButton' and @type='submit']";
    private $TANLabel = "//input[@name='tan']";
    private $yesButton = "//button[@id='yes']";

    protected function getOXID(): array
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
        $this->initializeTest();
        $this->choosePayment($this->giropayPaymentLabel);
        $this->submitOrder();

        $giropayPaymentData = Fixtures::get('giropay_payment');

        $I->waitForElement($this->banknameInput);
        $I->fillField($this->banknameInput, $giropayPaymentData['BIC']);
        $I->waitForElement($this->continueButton);
        $I->click($this->continueButton);

        $I->waitForElement($this->accountLabel);
        $I->fillField($this->accountLabel, $giropayPaymentData['USER']);
        $I->fillField($this->PINLabel, $giropayPaymentData['USER_PIN']);
        $I->waitForElement($this->continueButton);
        $I->click($this->continueButton);

        $I->waitForElement($this->TANLabel);
        $I->fillField($this->TANLabel, $giropayPaymentData['USER_TAN']);

        $I->waitForElement($this->continueButton);
        $I->click($this->continueButton);
        $I->waitForText(str_replace(',', '.', $this->getPrice()));
        $I->waitForElementClickable($this->continueButton);
        $I->wait(5);
        $I->click($this->continueButton);
        $I->wait(5);

        $this->checkSuccessfulPayment();
    }
}
