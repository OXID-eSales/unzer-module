<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

declare(strict_types=1);

namespace OxidSolutionCatalysts\Unzer\Tests\Codeception\Page;

use OxidEsales\Codeception\Page\Page;

class AlipayPage extends Page
{
    private $usernameField = "//input[@name='username']";
    private $passwordField = "//input[@name='password']";
    private $loginButton = 'Login';
    private $paymentMethodSelect = '#payment-method-select';
    private $makePaymentButton = 'Make Payment';
    private $paymentSuccessful = 'PAYMENT SUCCESSFUL';
    private $comeFrom = "Back to where you came from";

    /**
     * @param string $username login for alipay
     * @param string $password password for alipay
     * @param string $price price of order
     * @return void
     */
    public function login(string $username, string $password, string $price)
    {
        $I = $this->user;

        $I->waitForText($price);
        $I->waitForElement($this->usernameField);
        $I->fillField($this->usernameField, $username);
        $I->fillField($this->passwordField, $password);
        $I->click($this->loginButton);
    }

    /**
     * @param int $methodNumber number of select
     * @return void
     */
    public function choosePaymentMethod(int $methodNumber)
    {
        $I = $this->user;

        $I->waitForElement($this->paymentMethodSelect);
        $I->selectOption($this->paymentMethodSelect, $methodNumber);
        $I->click($this->makePaymentButton);
    }

    /**
     * @param string $price price of order
     * @return void
     */
    public function paymentSuccessful(string $price)
    {
        $I = $this->user;

        $I->waitForText($price);
        $I->waitForText($this->paymentSuccessful);
        $I->click($this->comeFrom);
    }
}
