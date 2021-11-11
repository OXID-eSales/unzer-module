<?php
/**
 * This Software is the property of OXID eSales and is protected
 * by copyright law - it is NOT Freeware.
 *
 * Any unauthorized use of this software without a valid license key
 * is a violation of the license agreement and will be prosecuted by
 * civil and criminal law.
 *
 * @copyright 2003-2021 OXID eSales AG
 * @author    OXID Solution Catalysts
 * @link      https://www.oxid-esales.com
 */

namespace OxidSolutionCatalysts\Unzer\Controller;

use OxidEsales\Eshop\Core\Registry;
use UnzerSDK\Exceptions\UnzerApiException;

class OrderController extends OrderController_parent
{
    /**
     * Checks for order rules confirmation ("ord_agb", "ord_custinfo" form values)(if no
     * rules agreed - returns to order view), loads basket contents (plus applied
     * price/amount discount if available - checks for stock, checks user data (if no
     * data is set - returns to user login page). Stores order info to database
     * (\OxidEsales\Eshop\Application\Model\Order::finalizeOrder()). According to sum for items automatically assigns
     * user to special user group ( \OxidEsales\Eshop\Application\Model\User::onOrderExecute(); if this option is not
     * disabled in admin). Finally you will be redirected to next page (order::_getNextStep()).
     *
     * @return string
     */
    public function execute(): string
    {
        try{
            $Dispatcher = oxnew(DispatcherController::class);
            $Dispatcher->validatePayment();
        }catch (UnzerApiException | \RuntimeException $e) {
            Registry::getUtilsView()->addErrorToDisplay($e);
        }

        return parent::execute();
    }
}
