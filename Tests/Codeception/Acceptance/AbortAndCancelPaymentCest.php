<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

declare(strict_types=1);

namespace OxidEsales\EshopCommunity\modules\osc\unzer\Tests\Codeception\Acceptance;

use Codeception\Configuration;
use Codeception\Util\Fixtures;
use OxidEsales\Codeception\Admin\AdminPanel;
use OxidEsales\Codeception\Module\Translation\Translator;
use OxidSolutionCatalysts\Unzer\Tests\Codeception\Acceptance\BaseCest;
use OxidSolutionCatalysts\Unzer\Tests\Codeception\AcceptanceTester;
use function OxidEsales\Codeception\Admin\AdminPanel;

/**
 * @group unzer_module
 * @group SecondGroup
 */
final class AbortAndCancelPaymentCest extends BaseCest
{
    public $searchForm = '#search';
    public $orderNumberInput = 'where[oxorder][oxordernr]';
    public string $userAccountLoginName = '#usr';
    public string $userAccountLoginPassword = '#pwd';
    public string $userAccountLoginButton = '.btn';
    private string $acceptAllCookiesButton = "//button[@id='acceptAllButton']";
    private string $paypalPaymentLabel = "//label[@for='payment_oscunzer_paypal']";
    private string $alipayPaymentLabel = "//label[@for='payment_oscunzer_alipay']";
    private string $loginInput = "#email";


    protected function _getOXID(): array
    {
        return ['oscunzer_paypal'];
    }

    /**
     * @group AbortAndCancelPaymentCest
     */
    public function testUserClicksBrowserBackOnPaypal(AcceptanceTester $I)
    {
        $I->wantToTest(
            'if order is saved and marked in backend if a user clicks "Go Back" in the browser on Paypal'
        );
        $this->_initializeTest();
        $orderPage = $this->_choosePayment($this->paypalPaymentLabel);
        $currentUrl = $I->grabFromCurrentUrl();
        $orderPage->submitOrder();

        $I->waitForDocumentReadyState();
        $I->wait(5);
        if ($this->_checkElementExists($this->acceptAllCookiesButton, $I)) {
            $I->click($this->acceptAllCookiesButton);
        }

        // login page
        $I->waitForDocumentReadyState();
        $I->makeScreenshot("before_back");
        $I->waitForElement($this->loginInput, 30);
        $I->amOnPage(Configuration::config()['modules']['config']['WebDriver']['url'] . $currentUrl);
        $I->makeScreenshot("after_back");
        $templateString = Translator::translate('OSCUNZER_CANCEL_DURING_CHECKOUT');
        $I->waitForDocumentReadyState();
        $I->makeScreenshot("after_back");
        $I->wait(5);
        $I->seeElement('.alert');
        $capturedFromScreen = $I->grabTextFrom('.alert');
        list($orderNumber) = sscanf($capturedFromScreen, str_replace('%s', '%d', $templateString));
        $this->finishTesting($I, $orderNumber, 'NOT_FINISHED', 'pending');
    }

    /**
     * @group AbortAndCancelPaymentCest
     */
    public function testUserClicksAbortOnPaypal(AcceptanceTester $I)
    {
        $I->wantToTest('if order is saved and marked in backend if a user clicks Abort on Paypal');
        $this->_initializeTest();
        $orderPage = $this->_choosePayment($this->paypalPaymentLabel);
        $orderPage->submitOrder();

        $I->waitForDocumentReadyState();
        $I->wait(5);
        if ($this->_checkElementExists($this->acceptAllCookiesButton, $I)) {
            $I->click($this->acceptAllCookiesButton);
        }

        // login page
        $I->waitForDocumentReadyState();
        $I->makeScreenshot("before_back");
        $I->waitForElement($this->loginInput, 30);
        $I->waitForElementClickable('//*[@id="cancelLink"]');
        $I->click('//*[@id="cancelLink"]');
        $I->makeScreenshot("after_back");
        $templateString = Translator::translate('OSCUNZER_CANCEL_DURING_CHECKOUT');
        $I->wait(10);
        $capturedFromScreen = $I->grabTextFrom(".alert");
        list($orderNumber) = sscanf($capturedFromScreen, str_replace('%s', '%d', $templateString));
        $this->finishTesting($I, $orderNumber, "CANCELED", "canceled");
    }

    /**
     * @group AbortAndCancelPaymentCest
     */
    public function testUserClicksAbortOnAlipay(AcceptanceTester $I)
    {
        $I->wantToTest('if order is saved and marked in backend if a user clicks Abort on Alipay');
        $this->_initializeTest();
        $orderPage = $this->_choosePayment($this->alipayPaymentLabel);
        $orderPage->submitOrder();

        $I->wait(5);
        if ($this->_checkElementExists($this->acceptAllCookiesButton, $I)) {
            $I->click($this->acceptAllCookiesButton);
        }

        // login page
        $I->waitForDocumentReadyState();
        $I->makeScreenshot("before_back");
        $I->see('Login to your Wallet');
        $I->waitForElementClickable('//*[@id="col-transaction-payment"]/div/div[2]/div[2]/div[1]/button');
        $I->click('//*[@id="col-transaction-payment"]/div/div[2]/div[2]/div[1]/button');
        $I->makeScreenshot("after_back");
        $templateString = Translator::translate('OSCUNZER_CANCEL_DURING_CHECKOUT');
        $I->wait(10);
        $capturedFromScreen = $I->grabTextFrom(".alert");
        list($orderNumber) = sscanf($capturedFromScreen, str_replace('%s', '%d', $templateString));
        $this->finishTesting($I, $orderNumber, "CANCELED", "canceled");
    }

    private function finishTesting(
        AcceptanceTester $I,
        int $orderNumber,
        string $orderStatus,
        string $transactionStatus
    ): void {
        $I->amOnPage('/admin/');
        $I->wait(10);
        $adminUser = Fixtures::get('admin_user');
        $I->fillField($this->userAccountLoginName, $adminUser['username']);
        $I->fillField($this->userAccountLoginPassword, $adminUser['password']);
        $I->click($this->userAccountLoginButton);
        $I->selectNavigationFrame();
        $I->click('//*[@id="nav-1-6"]/a');
        $I->waitForText('Orders');
        $I->click('//*[@id="nav-1-6-1"]/a');
        $I->selectListFrame();
        $I->fillField($this->orderNumberInput, $orderNumber);
        $I->submitForm($this->searchForm, []);
        $I->selectListFrame();
        $I->click($orderNumber);
        $I->selectEditFrame();
        $I->makeScreenshot('admin_order_main_page');
        $I->see($orderStatus);
        $I->selectListFrame();
        $I->click("/html/body/div[2]/div[3]/table/tbody/tr/td[7]/div/div/a");
        $I->selectEditFrame();
        $I->wait(10);
        $I->makeScreenshot('admin_order_unzer_page');
        $I->see($transactionStatus);
    }
}
