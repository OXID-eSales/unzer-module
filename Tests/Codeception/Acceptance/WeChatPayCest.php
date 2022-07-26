<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

declare(strict_types=1);

namespace OxidSolutionCatalysts\Unzer\Tests\Codeception\Acceptance;

use Codeception\Util\Fixtures;
use OxidSolutionCatalysts\Unzer\Tests\Codeception\AcceptanceTester;
use OxidSolutionCatalysts\Unzer\Tests\Codeception\Page\LocalPaymentMethodsSimulatorPage;

/**
 * @group unzer_module
 */
final class WeChatPayCest extends BaseCest
{
    private $wechatpayPaymentLabel = "//label[@for='payment_oscunzer_wechatpay']";

    protected function _getOXID(): array
    {
        return ['oscunzer_wechatpay'];
    }

    /**
     * @param AcceptanceTester $I
     * @group WechatpayPaymentTest
     */
    private function _prepareWechatpayTest(AcceptanceTester $I)
    {
        $this->_initializeTest();
        $orderPage = $this->_choosePayment($this->wechatpayPaymentLabel);
        $orderPage->submitOrder();
    }

    /**
     * @param AcceptanceTester $I
     * @group WechatpayPaymentTest
     */
    private function _checkWechatpayPayment(int $methodNumber)
    {
        $price = str_replace(',', '.', $this->_getPrice());
        $wechatpayClientData = Fixtures::get('wechatpay_client');
        $WechatpayPage = new LocalPaymentMethodsSimulatorPage($this->_getAcceptance());

        $WechatpayPage->login($wechatpayClientData['username'], $wechatpayClientData['password'], $price);
        $WechatpayPage->choosePaymentMethod($methodNumber);
        $WechatpayPage->paymentSuccessful($price);

        $this->_checkSuccessfulPayment();
    }

    /**
     * @param AcceptanceTester $I
     * @group WechatpayPaymentTest
     */
    public function checkWalletBalancePaymentWorks(AcceptanceTester $I)
    {
        $I->wantToTest('Test WeChatPay Wallet Balance payment works');
        $this->_prepareWechatpayTest($I);
        $this->_checkWechatpayPayment(1);
    }

    /**
     * @param AcceptanceTester $I
     * @group WechatpayPaymentTest
     */
    public function checkSomeLPMPaymentWorks(AcceptanceTester $I)
    {
        $I->wantToTest('Test WeChatPay Some LPM payment works');
        $this->_prepareWechatpayTest($I);
        $this->_checkWechatpayPayment(2);
    }

    /**
     * @param AcceptanceTester $I
     * @group WechatpayPaymentTest
     */
    public function checkAnotherLPMPaymentWorks(AcceptanceTester $I)
    {
        $I->wantToTest('Test WeChatPay Another LPM Wechatpay payment works');
        $this->_prepareWechatpayTest($I);
        $this->_checkWechatpayPayment(3);
    }

    /**
     * @param AcceptanceTester $I
     * @group WechatpayPaymentTest
     */
    public function checkOneMoreLPMPaymentWorks(AcceptanceTester $I)
    {
        $I->wantToTest('Test WeChatPay One more LPM Wechatpay payment works');
        $this->_prepareWechatpayTest($I);
        $this->_checkWechatpayPayment(4);
    }
}
