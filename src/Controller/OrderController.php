<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
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

    protected $blSepaMandateConfirmError = null;

    /**
     * @inerhitDoc
     */
    public function execute()
    {
        if (!$this->isSepaConfirmed()) {
            return;
        }

        $ret = parent::execute();
        if (str_starts_with($ret, 'thankyou')) {
            $this->saveUnzerTransaction();
        }

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
                $oOrder = oxNew(Order::class);

                $oOrder->load(Registry::getSession()->getVariable('sess_challenge'));
                //finalizing ordering process (validating, storing order into DB, executing payment, setting status ...)
                $iSuccess = (int)$oOrder->finalizeUnzerOrderAfterRedirect($oBasket, $oUser);

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
    public function isSepaMandateConfirmationError()
    {
        return $this->blSepaMandateConfirmError;
    }

    /**
     * @return bool|null
     */
    public function isSepaConfirmed(): ?bool
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

    public function saveUnzerTransaction(): void
    {
        $oOrder = oxNew(Order::class);
        if ($oOrder->load(Registry::getSession()->getVariable('sess_challenge'))) {
            $oOrder->initWriteTransactionToDB();
        }
    }
}
