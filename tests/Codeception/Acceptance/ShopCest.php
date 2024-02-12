<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

declare(strict_types=1);

namespace OxidSolutionCatalysts\Unzer\Tests\Codeception\Acceptance;

use OxidEsales\Codeception\Module\Translation\Translator;
use OxidEsales\Codeception\Page\Home;
use OxidSolutionCatalysts\Unzer\Tests\Codeception\AcceptanceTester;

/**
 * @group unzer_module
 * @group PaymentAvailableTest
 */
final class ShopCest extends BaseCest
{
    protected function _getOXID(): array
    {
        return [];
    }

    /**
     * @param AcceptanceTester $I
     * @group ShopOpenTest
     */
    public function shopStartPageLoads(AcceptanceTester $I)
    {
        $homePage = new Home($I);
        $I->amOnPage($homePage->URL);

        $I->waitForText(Translator::translate('OUR_BRANDS'));
        $I->waitForText(Translator::translate('NEWSLETTER'));
    }
}
