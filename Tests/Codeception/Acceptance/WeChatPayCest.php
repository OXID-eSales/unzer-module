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

    protected function getOXID(): array
    {
        return ['oscunzer_wechatpay'];
    }

    /**
     * @param AcceptanceTester $I
     * @group WechatpayPaymentTest
     */
    private function prepareWechatpayTest(AcceptanceTester $I)
    {
        $this->initializeTest();
        $orderPage = $this->choosePayment($this->wechatpayPaymentLabel);
        $orderPage->submitOrder();
    }

    /**
     * @param AcceptanceTester $I
     * @group WechatpayPaymentTest
     */
    private function checkWechatpayPayment(int $methodNumber)
    {
        $price = str_replace(',', '.', $this->getPrice());
        $weChatPayClientData = Fixtures::get('wechatpay_client');
        $this->I->wait(30);
        $weChatPayPage = new LocalPaymentMethodsSimulatorPage($this->I);
        $weChatPayPage->login($weChatPayClientData['username'], $weChatPayClientData['password'], $price);
        $weChatPayPage->choosePaymentMethod($methodNumber);
        $weChatPayPage->paymentSuccessful($price);

        $this->checkSuccessfulPayment();
    }

    /**
     * @param AcceptanceTester $I
     * @group WechatpayPaymentTest
     */
    public function checkWalletBalancePaymentWorks(AcceptanceTester $I)
    {
        $I->wantToTest('Test WeChatPay Wallet Balance payment works');
        $this->prepareWechatpayTest($I);
        $this->checkWechatpayPayment(1);
    }

    /**
     * @param AcceptanceTester $I
     * @group WechatpayPaymentTest
     */
    public function checkSomeLPMPaymentWorks(AcceptanceTester $I)
    {
        $I->wantToTest('Test WeChatPay Some LPM payment works');
        $this->prepareWechatpayTest($I);
        $this->checkWechatpayPayment(2);
    }

    /**
     * @param AcceptanceTester $I
     * @group WechatpayPaymentTest
     */
    public function checkAnotherLPMPaymentWorks(AcceptanceTester $I)
    {
        $I->wantToTest('Test WeChatPay Another LPM Wechatpay payment works');
        $this->prepareWechatpayTest($I);
        $this->checkWechatpayPayment(3);
    }

    /**
     * @param AcceptanceTester $I
     * @group WechatpayPaymentTest
     */
    public function checkOneMoreLPMPaymentWorks(AcceptanceTester $I)
    {
        $I->wantToTest('Test WeChatPay One more LPM Wechatpay payment works');
        $this->prepareWechatpayTest($I);
        $this->checkWechatpayPayment(4);
    }
}
