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

class StandardCestHelper
{
    protected string $basketModalCloseButtonSelector = '#basketModal button.close';
    protected string $toCompleteAuthentication = "Click here to complete authentication.";

    public function openShop(AcceptanceTester $I): Home
    {
        return $I->openShop();
    }

    public function login(Home $homePage)
    {
        $clientFixtures = $this->getClientFixtures();
        $homePage->loginUser($clientFixtures['username'], $clientFixtures['password']);
    }

    public function openAccountPage(Home $homePage, AcceptanceTester $I)
    {
        $homePage->openAccountPage();
        $I->click("//*[@id='account_menu']/ul/li[1]/a");
        $clientFixtures = $this->getClientFixtures();
        $I->see($clientFixtures['username']);
    }

    public function addProductToBasket(AcceptanceTester $I, int $amount = 1)
    {
        $basketItem = $this->getProductFixtures();
        $basketSteps = new BasketSteps($I);
        $basketSteps->addProductToBasket($basketItem['id'], $amount);
    }

    /**
     * Open checkout page.
     * If user is logged in, open PaymentCheckout page.
     * If user is not logged in, open UserCheckout page.
     *
     * @return UserCheckout|PaymentCheckout
     */
    public function openCheckout(Home $homePage)
    {
        return $homePage->openMiniBasket()->openCheckout();
    }

    public function choosePayment(string $label, Page $paymentCheckoutPage, AcceptanceTester $I): Page
    {
        $I->waitForElementClickable($label);
        $I->click($label);

        return $paymentCheckoutPage->goToNextStep();
    }

    public function checkSuccessfulPayment(AcceptanceTester $I, int $longWait = 0)
    {
        $I->wait(1);
        $I->waitForDocumentReadyState();
        $I->wait(1);
        $I->waitForPageLoad();
        $I->wait(1 + $longWait);
        $I->waitForText(Translator::translate('THANK_YOU'));
    }

    public function checkCreditCardPayment(AcceptanceTester $I, int $longWait = 0): void
    {
        $I->waitForText($this->toCompleteAuthentication, 60);
        $I->click($this->toCompleteAuthentication);
        $this->checkSuccessfulPayment($I, $longWait);
    }

    public function getProductFixtures(): array
    {
        return Fixtures::get('product');
    }

    public function getClientFixtures(): array
    {
        return Fixtures::get('client');
    }

    public function getSepaPaymentFixtures(): array
    {
        return Fixtures::get('sepa_payment');
    }
}
