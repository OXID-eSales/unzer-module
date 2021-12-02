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
use OxidSolutionCatalysts\Unzer\Core\UnzerHelper;

class OrderController extends OrderController_parent
{
    /**
     * Config option "blConfirmSEPA"
     *
     * @var bool
     */
    protected $blSepaMandateConfirmError = null;

    public function getUnzerPubKey()
    {
        /** @var \OxidSolutionCatalysts\Unzer\Service\ModuleSettings $settings */
        $settings = $this->getContainer()->get(\OxidSolutionCatalysts\Unzer\Service\ModuleSettings::class);
        return $settings->getShopPublicKey();
    }

    /**
     * Checks for order rules confirmation ("ord_agb", "ord_custinfo", "sepaConfirmation" form values)(if no
     * rules agreed - returns to order view), loads basket contents (plus applied
     * price/amount discount if available - checks for stock, checks user data (if no
     * data is set - returns to user login page). Stores order info to database
     * (\OxidEsales\Eshop\Application\Model\Order::finalizeOrder()). According to sum for items automatically assigns
     * user to special user group ( \OxidEsales\Eshop\Application\Model\User::onOrderExecute(); if this option is not
     * disabled in admin). Finally you will be redirected to next page (order::_getNextStep()).
     *
     * @return string
     */
    public function execute()
    {
        $foundIssue = false;
        $result = '';

        if (!$this->getSession()->checkSessionChallenge()) {
            $foundIssue = true;
        }

        if (!$this->_validateTermsAndConditions()) {
            $this->_blConfirmAGBError = true;
            $foundIssue = true;
        }

        // additional check if we really really have a user now
        $oUser = $this->getUser();
        if (!$oUser) {
            $foundIssue = true;
            $result = 'user';
        }

        if ($this->getPayment()->getId() === 'oscunzer_sepa') {
            $blSepaMandateConfirm = Registry::getRequest()->getRequestParameter('sepaConfirmation');
            if (!$blSepaMandateConfirm) {
                $this->blSepaMandateConfirmError = true;
                $foundIssue = true;
            }
        }

        if (!$foundIssue) {
            $result = parent::execute();
        }

        return $result;
    }

    public function isSepaMandateConfirmationError()
    {
        return $this->blSepaMandateConfirmError;
    }
}
