<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

declare(strict_types=1);

namespace OxidSolutionCatalysts\Unzer\Tests\Codeception\Acceptance;

use Codeception\Util\Fixtures;
use OxidSolutionCatalysts\Unzer\Tests\Codeception\AcceptanceTester;

final class SEPADirectDebitSecuredCest extends BaseCest
{
    private $sepaPaymentLabel = "//label[@for='payment_oscunzer_sepa-secured']";
    private $IBANInput = "//input[contains(@id, 'unzer-iban-input')]";

    /**
     * @param AcceptanceTester $I
     * @group SEPADirectSecuredPaymentTest
     */
    public function checkPaymentWorks(AcceptanceTester $I)
    {
        $I->wantToTest('Test SEPA Direct Debit payment works');
        $this->_setAcceptance($I);
        $this->_initializeSecuredTest();
        $orderPage = $this->_choosePayment($this->sepaPaymentLabel);

        $payment = Fixtures::get('sepa_payment');
        $I->fillField($this->IBANInput, $payment['IBAN']);
        $I->click("#oscunzersepaagreement");
        $I->wait(1);

        $orderPage->submitOrder();

        $I->waitForText($this->_getTranslator()->translate('THANK_YOU'));
    }
}
