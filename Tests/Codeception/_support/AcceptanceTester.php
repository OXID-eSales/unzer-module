<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

namespace OxidSolutionCatalysts\Unzer\Tests\Codeception;

use OxidEsales\Codeception\Page\Home;

/**
 * Inherited Methods
 *
 * @method void wantToTest($text)
 * @method void wantTo($text)
 * @method void execute($callable)
 * @method void expectTo($prediction)
 * @method void expect($prediction)
 * @method void amGoingTo($argumentation)
 * @method void am($role)
 * @method void lookForwardTo($achieveValue)
 * @method void comment($description)
 * @method void pause()
 *
 * @SuppressWarnings(PHPMD)
 */
class AcceptanceTester extends \Codeception\Actor
{
    use _generated\AcceptanceTesterActions;

    /**
     * Define custom actions here
     */

    public function saveShopConfVar($sVarType, $sVarName, $sVarVal, $sShopId = null, $sModule = '')
    {
        $config = \OxidEsales\Eshop\Core\Registry::getConfig();
        $config->saveShopConfVar($sVarType, $sVarName, $sVarVal, $sShopId, $sModule);
    }

    /**
     * Open shop first page.
     */
    public function openShop()
    {
        $I = $this;
        $homePage = new Home($I);
        $I->amOnPage($homePage->URL);

        return $homePage;
    }

    public function retryClick($link, $context = null)
    {
        $I = $this;
        $retryNum = 3; // Number of retries
        $retryInterval = 1000; // Interval in milliseconds

        for ($i = 0; $i < $retryNum; $i++) {
            try {
                $I->waitForElementClickable($link, $context);
                $I->click($link, $context);
                return;
            } catch (\Exception $e) {
                if ($i == $retryNum - 1) {
                    throw $e;
                }
                usleep($retryInterval * 1000); // Convert to microseconds
            }
        }
    }
}
