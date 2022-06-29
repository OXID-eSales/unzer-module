<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

declare(strict_types=1);

namespace OxidSolutionCatalysts\Unzer\Tests\Codeception\Acceptance;

use Codeception\Util\Fixtures;
use OxidSolutionCatalysts\Unzer\Tests\Codeception\AcceptanceTester;

class EPSCest extends BaseCest
{
    private $epsLabel = "//label[@for='payment_oscunzer_eps']";
    private $paymentMethodForm = "//form[@id='payment-form']";
    private $usernameInput = "//input[@id='username']";
    private $passwordInput = "//input[@id='passwort']";
    private $submitInput = "//input[@type='submit']";
    private $tanSpan = "//span[@id='tan']";
    private $tanInput = "//input[@id='usrtan']";
    private $backlinkDiv = "//div[@class='button']";

    /**
     * @param AcceptanceTester $I
     * @group EPSPaymentTest
     */
    public function checkPaymentWorks(AcceptanceTester $I)
    {
        $I->wantToTest('Test PayPal payment works');
        $I->updateInDatabase('oxpayments', ['OXACTIVE' => 1], ['OXID' => 'oscunzer_eps']);
        $I->updateInDatabase(
            'oxobject2payment',
            ['OXOBJECTID' => '	a7c40f631fc920687.20179984'],
            ['OXPAYMENTID' => 'oscunzer_eps', 'OXTYPE' => 'oxcountry']
        );
        $this->_setAcceptance($I);
        $this->_initializeTest();
        $orderPage = $this->_choosePayment($this->epsLabel);

        $epsPaymentData = Fixtures::get('eps_payment');

        $I->waitForElement($this->paymentMethodForm);
        $I->click($this->paymentMethodForm);
        $I->click("//div[@data-value='" . $epsPaymentData["option"] . "']");
        $orderPage->submitOrder();

        // first page : login
        $I->waitForElement($this->usernameInput, 20);
        $I->fillField($this->usernameInput, $epsPaymentData["username"]);
        $I->fillField($this->passwordInput, $epsPaymentData["password"]);
        $I->click($this->submitInput);

        // second page : check data
        $I->waitForElement($this->submitInput);
        $I->click("//input[@type='submit' and @value=' TAN ANFORDERN ']");

        // third page : confirm button
        $I->waitForElement($this->tanSpan);
        $tan = $I->grabTextFrom($this->tanSpan);
        $I->fillField($this->tanInput, $tan);
        $I->click($this->submitInput);

        $I->click($this->backlinkDiv);
        $I->waitForText($this->_getTranslator()->translate('THANK_YOU'));
    }
}
