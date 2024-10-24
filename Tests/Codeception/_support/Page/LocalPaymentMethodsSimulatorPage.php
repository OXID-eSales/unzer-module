<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

declare(strict_types=1);

namespace OxidSolutionCatalysts\Unzer\Tests\Codeception\Page;

use OxidEsales\Codeception\Page\Page;

class LocalPaymentMethodsSimulatorPage extends Page
{
    private string $usernameField = "//input[@name='username']";
    private string $passwordField = "//input[@name='password']";
    private string $nextButton = "//button[@class='btn btn-primary']";
    private string $paymentMethodSelect = '#payment-method-select';
    private string $paymentSuccessful = 'PAYMENT SUCCESSFUL';

    /**
     * @param string $username login for alipay
     * @param string $password password for alipay
     * @param string $price price of order
     * @return void
     */
    public function login(string $username, string $password, string $price)
    {
        $I = $this->user;

        $I->waitForElement($this->usernameField);
        $I->fillField($this->usernameField, $username);
        $I->fillField($this->passwordField, $password);
        $I->click($this->nextButton);
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
        $I->click($this->nextButton);
    }

    /**
     * @param string $price price of order
     * @return void
     */
    public function paymentSuccessful(string $price)
    {
        $I = $this->user;
        $I->wait(60);
        $I->makeScreenshot("wwechat_payment");
        $I->waitForText($price);
        $I->waitForText($this->paymentSuccessful);
        $I->waitForElement($this->nextButton);
        $I->click($this->nextButton);
    }
}
