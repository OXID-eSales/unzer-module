<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

declare(strict_types=1);

namespace OxidSolutionCatalysts\Unzer\Tests\Codeception\Acceptance;

use Codeception\Util\Fixtures;
use OxidEsales\Codeception\Page\Checkout\OrderCheckout;
use OxidEsales\Eshop\Core\Registry;
use OxidEsales\Codeception\Page\Page;
use OxidEsales\Codeception\Step\Basket as BasketSteps;
use OxidSolutionCatalysts\Unzer\Tests\Codeception\AcceptanceTester;
use OxidEsales\Codeception\Module\Translation\Translator;

abstract class BaseCest
{
    private int $amount = 1;
    private AcceptanceTester $I;
    private Page $paymentSelection;

    public function _before(AcceptanceTester $I): void
    {
        foreach ($this->_getOXID() as $payment) {
            $I->updateInDatabase(
                'oxpayments',
                ['OXACTIVE' => 1],
                ['OXID' => $payment]
            );

            $I->haveInDatabase(
                'oxobject2payment',
                [
                    'OXID' => 'test' . $payment,
                    'OXOBJECTID' => 'a7c40f631fc920687.20179984',
                    'OXPAYMENTID' => $payment,
                    'OXTYPE' => 'oxcountry'
                ]
            );
        }

        $this->I = $I;
    }

    public function _after(AcceptanceTester $I): void
    {
        $I->clearShopCache();
    }

    /**
     * @return void
     */
    protected function _initializeTest()
    {
        $miniBasketMenuElement = '//div[@class="btn-group minibasket-menu"]/button';
        $this->I->openShop();

        $basketItem = Fixtures::get('product');

        $params['fnc'] = 'tobasket';
        $params['aid'] = $basketItem['id'];
        $params['am'] = $this->amount;
        $params['anid'] = $basketItem['id'];

        if ($this->I->seePageHasElement('input[name=stoken]')) {
            $params['stoken'] = $this->I->grabValueFrom('input[name=stoken]');
        }

        if ($this->I->seePageHasElement('input[name=force_sid]')) {
            $params['force_sid'] = $this->I->grabValueFrom('input[name=force_sid]');
        }

        $this->I->amOnPage('/index.php?' . http_build_query($params));
        $this->I->waitForElement($miniBasketMenuElement);
        $this->I->waitForPageLoad();

        $this->_loginUser();

        $this->I->waitForElementClickable($miniBasketMenuElement);
        $this->I->click($miniBasketMenuElement);

        $this->I->waitForText(Translator::translate('DISPLAY_BASKET'));
        $this->I->click(Translator::translate('DISPLAY_BASKET'));
        $this->I->waitForPageLoad();

        $this->I->waitForText(Translator::translate('CONTINUE_TO_NEXT_STEP'));
        $this->I->click(Translator::translate('CONTINUE_TO_NEXT_STEP'));
        $this->I->waitForPageLoad();

        $this->I->waitForText(Translator::translate('CONTINUE_TO_NEXT_STEP'));
        $this->I->click(Translator::translate('CONTINUE_TO_NEXT_STEP'));
        $this->I->waitForPageLoad();
    }

    /**
     * @return void
     */
    protected function _initializeSecuredTest()
    {
        $miniBasketMenuElement = '//div[@class="btn-group minibasket-menu"]/button';
        $this->I->openShop();

        $basketItem = Fixtures::get('product');

        $params['fnc'] = 'tobasket';
        $params['aid'] = $basketItem['id'];
        $params['am'] = $this->amount;
        $params['anid'] = $basketItem['id'];

        if ($this->I->seePageHasElement('input[name=stoken]')) {
            $params['stoken'] = $this->I->grabValueFrom('input[name=stoken]');
        }

        if ($this->I->seePageHasElement('input[name=force_sid]')) {
            $params['force_sid'] = $this->I->grabValueFrom('input[name=force_sid]');
        }

        $this->I->amOnPage('/index.php?' . http_build_query($params));
        $this->I->waitForElement($miniBasketMenuElement);
        $this->I->waitForPageLoad();

        $this->_loginUser();

        $this->I->waitForElementClickable($miniBasketMenuElement);
        $this->I->click($miniBasketMenuElement);

        $this->I->waitForText(Translator::translate('DISPLAY_BASKET'));
        $this->I->click(Translator::translate('DISPLAY_BASKET'));
        $this->I->waitForPageLoad();

        $this->I->waitForText(Translator::translate('CONTINUE_TO_NEXT_STEP'));
        $this->I->click(Translator::translate('CONTINUE_TO_NEXT_STEP'));
        $this->I->waitForPageLoad();

        $this->I->waitForText(Translator::translate('CONTINUE_TO_NEXT_STEP'));
        $this->I->click(Translator::translate('CONTINUE_TO_NEXT_STEP'));
        $this->I->waitForPageLoad();
    }

    protected function _loginUser()
    {
        $accountMenuButton = "//div[contains(@class,'service-menu')]/button";
        $openAccountMenuButton = "//div[contains(@class,'service-menu')]/ul";
        $userLoginName = '#loginEmail';
        $userLoginPassword = '#loginPasword';
        $userLoginButton = '//div[@id="loginBox"]/button';

        $clientData = Fixtures::get('secured_client');

        $this->I->waitForPageLoad();
        $this->I->waitForElementVisible($accountMenuButton);
        $this->I->click($accountMenuButton);
        $this->I->waitForElementClickable($openAccountMenuButton);

        $this->I->waitForText(Translator::translate('MY_ACCOUNT'));
        $this->I->waitForElementVisible($userLoginName);
        $this->I->fillField($userLoginName, $clientData['username']);
        $this->I->fillField($userLoginPassword, $clientData['password']);
        $this->I->click($userLoginButton);
        $this->I->waitForPageLoad();
    }

    /**
     * @param string $label
     * @return void
     */
    protected function _choosePayment(string $label)
    {
        $nextStepButton = '#paymentNextStepBottom';
        $breadCrumb = '#breadcrumb';

        $this->I->waitForElement($label);
        $this->I->click($label);

        $this->I->click($nextStepButton);
        $this->I->waitForElement($breadCrumb);
    }

    /**
     * @return void
     */
    protected function _checkSuccessfulPayment()
    {
        $this->I->waitForDocumentReadyState();
        $this->I->waitForPageLoad();
        $this->I->waitForText(Translator::translate('THANK_YOU'));
    }

    /**
     * @return void
     */
    protected function _submitOrder()
    {
        $this->I->waitForText(Translator::translate('SUBMIT_ORDER'));
        $this->I->click(Translator::translate('SUBMIT_ORDER'));
        $this->I->waitForPageLoad();
    }

    /**
     * @param AcceptanceTester $I
     * @return void
     */
    protected function _setAcceptance(AcceptanceTester $I)
    {
        $this->I = $I;
    }

    /**
     * @return AcceptanceTester
     */
    protected function _getAcceptance(): AcceptanceTester
    {
        return $this->I;
    }

    /**
     * @return string price of order
     */
    protected function _getPrice(): string
    {
        $basketItem = Fixtures::get('product');
        return Registry::getLang()->formatCurrency(
            $basketItem['bruttoprice_single'] * $this->amount + $basketItem['shipping_cost']
        );
    }

    /**
     * @return string currency
     */
    protected function _getCurrency(): string
    {
        $basketItem = Fixtures::get('product');
        return $basketItem['currency'];
    }

    abstract protected function _getOXID(): array;

    /**
     * If element is found return the text, if not return false
     * @param $element
     * @return bool
     */
    protected function _grabTextFromElementWhenPresent($element, $I)
    {
        try {
            $I->seeElement($element);
            $isFound = $I->grabTextFrom($element);
        } catch (\Exception $e) {
            $isFound = false;
        }
        return $isFound;
    }

    /**
     * @param $element
     * @return bool
     */
    protected function _checkElementExists($element, $I)
    {
        try {
            $isFound = $I->seeElement($element);
        } catch (\Exception $e) {
            $isFound = false;
        }
        return $isFound;
    }
}
