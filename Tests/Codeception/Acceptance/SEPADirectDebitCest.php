<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

declare(strict_types=1);

namespace OxidSolutionCatalysts\Unzer\Tests\Codeception\Acceptance;

use Codeception\Util\Fixtures;
use OxidSolutionCatalysts\Unzer\Tests\Codeception\AcceptanceTester;

/**
 * @group unzer_module
 * @group SecondGroup
 */
final class SEPADirectDebitCest extends AbstractSepaCest
{
    /**
     * @param AcceptanceTester $I
     * @group SEPADirectPaymentTest
     */
    public function checkPaymentWorks(AcceptanceTester $I)
    {
        $I->wantToTest('Test SEPA Direct Debit payment works');
        $this->initializeTest();
        $orderPage = $this->choosePayment($this->sepaPaymentLabel);

        $payment = Fixtures::get('sepa_payment');
        $I->fillField($this->IBANInput, $payment['IBAN']);
        $I->click("#oscunzersepaagreement");
        $I->wait(1);

        $orderPage->submitOrder();

        $this->checkSuccessfulPayment();
    }
}
