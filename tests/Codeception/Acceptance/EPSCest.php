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
 * @group FirstGroup
 */
final class EPSCest extends BaseCest
{
    private $epsLabel = "//label[@for='payment_oscunzer_eps']";
    private $paymentMethodForm = "#payment-form";
    private $usernameInput = "//input[@id='username']";
    private $passwordInput = "//input[@id='passwort']";
    private $submitInput = "//input[@type='submit']";
    private $submitDataInput = "//input[@type='submit' and @value=' TAN ANFORDERN ']";
    private $submitPaymentInput = "//input[@type='submit' and @value=' TAN SENDEN ']";
    private $tanSpan = "//span[@id='tan']";
    private $tanInput = "//input[@id='usrtan']";
    private $backlinkDiv = "//div[@class='button']";
    private $submitButton = '#selectionSubmit';
    private $listboxUl = '#listbox-container';
    private $banknameInput = '#bankname';

    protected function getOXID(): array
    {
        return ['oscunzer_eps'];
    }

    public function _before(AcceptanceTester $I): void
    {
        parent::_before($I);

        // EPS is now only available in AT, BaseCest should make all the necessary setup
        // User is assigned to AT
        $user = Fixtures::get('client');
        $I->updateInDatabase(
            'oxuser',
            ['oxcountryid' => 'a7c40f6320aeb2ec2.72885259'], // AT
            ['oxusername' => $user['username']]
        );
    }

    public function _after(AcceptanceTester $I): void
    {
        $user = Fixtures::get('client');
        $I->updateInDatabase(
            'oxuser',
            ['oxcountryid' => 'a7c40f631fc920687.20179984'], // DE
            ['oxusername' => $user['username']]
        );
    }

    /**
     * @param AcceptanceTester $I
     * @group EPSPaymentTest
     */
    public function checkPaymentWorks(AcceptanceTester $I)
    {
        $I->markTestSkipped("EPS skipped temporary");

        $I->wantToTest('Test EPS payment works');
        $this->initializeTest();
        $this->choosePayment($this->epsLabel);

        $epsPaymentData = Fixtures::get('eps_payment');

        $I->waitForDocumentReadyState();
        $I->scrollTo($this->paymentMethodForm);
        $I->wait(3);
        $I->waitForElement($this->paymentMethodForm);
        $I->click($this->paymentMethodForm);
        $I->waitForDocumentReadyState();
        $I->waitForElement("//div[@data-value='" . $epsPaymentData["option"] . "']");
        $I->click("//div[@data-value='" . $epsPaymentData["option"] . "']");
        $this->submitOrder();

        // first page : login
        $I->waitForPageLoad();
        $I->waitForDocumentReadyState();
        $I->wait(5);
        $I->waitForElement($this->usernameInput);
        $I->fillField($this->usernameInput, $epsPaymentData["username"]);
        $I->waitForElement($this->passwordInput);
        $I->fillField($this->passwordInput, $epsPaymentData["password"]);
        $I->click($this->submitInput);

        // second page : check data
        $I->waitForPageLoad();
        $I->waitForDocumentReadyState();
        $I->waitForElement($this->submitDataInput);
        $I->click($this->submitDataInput);
        $I->wait(1);

        // third page : confirm button
        $I->waitForPageLoad();
        $I->waitForDocumentReadyState();
        $I->waitForElement($this->tanSpan);
        $tan = $I->grabTextFrom($this->tanSpan);
        $I->fillField($this->tanInput, $tan);
        $I->waitForElement($this->submitPaymentInput);
        $I->click($this->submitPaymentInput);

        $I->waitForPageLoad();
        $I->waitForDocumentReadyState();
        $I->click($this->backlinkDiv);
        $this->checkSuccessfulPayment();
    }
}
