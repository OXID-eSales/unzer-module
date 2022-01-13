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

use OxidEsales\Eshop\Application\Model\Order;
use OxidEsales\Eshop\Core\Exception\ArticleInputException;
use OxidEsales\Eshop\Core\Exception\NoArticleException;
use OxidEsales\Eshop\Core\Exception\OutOfStockException;
use OxidEsales\Eshop\Core\Registry;
use OxidSolutionCatalysts\Unzer\Exception\Redirect;
use OxidSolutionCatalysts\Unzer\Service\Unzer;
use OxidSolutionCatalysts\Unzer\Traits\ServiceContainer;

class OrderController extends OrderController_parent
{
    use ServiceContainer;

    /**
     * Config option "blConfirmSEPA"
     *
     * @var bool
     */
    protected $blSepaMandateConfirmError = null;

    /**
     * @inerhitDoc
     * Checks for order rules confirmation ("ord_agb", "ord_custinfo", "sepaConfirmation" form values)(if no
     * rules agreed - returns to order view), loads basket contents (plus applied
     * price/amount discount if available - checks for stock, checks user data (if no
     * data is set - returns to user login page). Stores order info to database
     * (\OxidEsales\Eshop\Application\Model\Order::finalizeOrder()). According to sum for items automatically assigns
     * user to special user group ( \OxidEsales\Eshop\Application\Model\User::onOrderExecute(); if this option is not
     * disabled in admin). Finally you will be redirected to next page (order::_getNextStep()).
     */
    public function execute()
    {
        if (!$this->isSepaConfirmed()) return;

        $ret = parent::execute();
        if ($ret == 'thankyou') $this->saveUnzerTransaction();

        return $ret;
    }

    /**
     * @throws Redirect
     */
    public function unzerExecuteAfterRedirect(): void
    {
        // get basket contents
        $oUser = $this->getUser();
        $oBasket = $this->getSession()->getBasket();
        if ($oBasket->getProductsCount()) {
            try {
                $oOrder = oxNew(\OxidEsales\Eshop\Application\Model\Order::class);

                //finalizing ordering process (validating, storing order into DB, executing payment, setting status ...)
                $iSuccess = $oOrder->finalizeUnzerOrderAfterRedirect($oBasket, $oUser);

                // performing special actions after user finishes order (assignment to special user groups)
                $oUser->onOrderExecute($oBasket, $iSuccess);

                $nextStep = $this->_getNextStep($iSuccess);

                // proceeding to next view
                $unzerService = $this->getServiceFromContainer(Unzer::class);
                throw new Redirect($unzerService->prepareRedirectUrl($nextStep));
            } catch (OutOfStockException $oEx) {
                $oEx->setDestination('basket');
                Registry::getUtilsView()->addErrorToDisplay($oEx, false, true, 'basket');
            } catch (NoArticleException | ArticleInputException $oEx) {
                Registry::getUtilsView()->addErrorToDisplay($oEx);
            }
        }
    }

    /**
     * @return bool|null
     */
    public function isSepaMandateConfirmationError(): ?bool
    {
        return $this->blSepaMandateConfirmError;
    }

    /**
     * @return bool
     */
    public function isSepaConfirmed(): bool
    {
        if ($this->getPayment()->getId() === 'oscunzer_sepa') {
            $blSepaMandateConfirm = Registry::getRequest()->getRequestParameter('sepaConfirmation');
            if (!$blSepaMandateConfirm) {
                $this->blSepaMandateConfirmError = true;
                return false;
            }
        }
        return true;
    }

    public function saveUnzerTransaction(): void{
        $oOrder = oxNew(Order::class);
        if($oOrder->load(Registry::getSession()->getVariable('sess_challenge'))){
            $oOrder->initWriteTransactionToDB();
        }
    }
}
