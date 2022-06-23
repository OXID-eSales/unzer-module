<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

declare(strict_types=1);

namespace OxidSolutionCatalysts\Unzer\Tests\Codeception\Acceptance;

use Codeception\Util\Fixtures;
use OxidSolutionCatalysts\Unzer\Tests\Codeception\AcceptanceTester;
use OxidSolutionCatalysts\Unzer\Tests\Codeception\Page\AlipayPage;

class WeChatPayCest extends BaseCest
{
    private $wechatpayPaymentLabel = "//label[@for='payment_oscunzer_wechatpay']";

    /**
     * @param AcceptanceTester $I
     * @group WechatpayPaymentTest
     */
    private function _prepareAlipayTest(AcceptanceTester $I)
    {
        $this->_setAcceptance($I);
        $this->_initializeTest();
        $orderPage = $this->_choosePayment($this->wechatpayPaymentLabel);
        $orderPage->submitOrder();
    }

    /**
     * @param AcceptanceTester $I
     * @group WechatpayPaymentTest
     */
    private function _checkAlipayPayment(int $methodNumber)
    {
        $price = str_replace($this->_getPrice(), ',', '.');
        $wechatpayClientData = Fixtures::get('wechatpay_client');
        $alipayPage = new AlipayPage($this->_getAcceptance());

        $alipayPage->login($wechatpayClientData['username'], $wechatpayClientData['password'], $price);
        $alipayPage->choosePaymentMethod($methodNumber);
        $alipayPage->paymentSuccessful($price);

        $this->_getAcceptance()->waitForText($this->_getTranslator()->translate('THANK_YOU'));
    }

    /**
     * @param AcceptanceTester $I
     * @group WechatpayPaymentTest
     */
    public function checkWalletBalancePaymentWorks(AcceptanceTester $I)
    {
        $I->wantToTest('Test WeChatPay Wallet Balance payment works');
        $this->_prepareAlipayTest($I);
        $this->_checkAlipayPayment(1);
    }

    /**
     * @param AcceptanceTester $I
     * @group WechatpayPaymentTest
     */
    public function checkSomeLPMPaymentWorks(AcceptanceTester $I)
    {
        $I->wantToTest('Test WeChatPay Some LPM payment works');
        $this->_prepareAlipayTest($I);
        $this->_checkAlipayPayment(2);
    }

    /**
     * @param AcceptanceTester $I
     * @group WechatpayPaymentTest
     */
    public function checkAnotherLPMPaymentWorks(AcceptanceTester $I)
    {
        $I->wantToTest('Test WeChatPay Another LPM Alipay payment works');
        $this->_prepareAlipayTest($I);
        $this->_checkAlipayPayment(3);
    }

    /**
     * @param AcceptanceTester $I
     * @group WechatpayPaymentTest
     */
    public function checkOneMoreLPMPaymentWorks(AcceptanceTester $I)
    {
        $I->wantToTest('Test WeChatPay One more LPM Alipay payment works');
        $this->_prepareAlipayTest($I);
        $this->_checkAlipayPayment(4);
    }
}
