<?php

namespace OxidSolutionCatalysts\Unzer\Tests\Codeception\Acceptance\Helper;

use Codeception\Util\Fixtures;
use OxidEsales\Codeception\Module\Translation\Translator;
use OxidEsales\Codeception\Page\Checkout\PaymentCheckout;
use OxidEsales\Codeception\Page\Checkout\UserCheckout;
use OxidEsales\Codeception\Page\Home;
use OxidEsales\Codeception\Page\Page;
use OxidEsales\Codeception\Step\Basket as BasketSteps;
use OxidSolutionCatalysts\Unzer\Tests\Codeception\AcceptanceTester;

class CreditCardCestHelper extends StandardCestHelper
{
    protected string $toCompleteAuthentication = "Click here to complete authentication.";

    public function checkCreditCardPayment(AcceptanceTester $I, int $longWait = 0): void
    {
        $I->waitForText($this->toCompleteAuthentication, 60);
        $I->click($this->toCompleteAuthentication);
        $this->checkSuccessfulPayment($I, $longWait);
    }
}
