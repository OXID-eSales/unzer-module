<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

namespace OxidSolutionCatalysts\Unzer\Controller;

use OxidEsales\Eshop\Application\Controller\AccountController;
use OxidSolutionCatalysts\Unzer\Service\SavedPaymentLoadService;
use OxidSolutionCatalysts\Unzer\Service\SavedPaymentSaveService;
use OxidSolutionCatalysts\Unzer\Service\View\SavedPaymentViewService;
use OxidSolutionCatalysts\Unzer\Traits\Request;
use OxidSolutionCatalysts\Unzer\Traits\ServiceContainer;

class AccountSavedPaymentController extends AccountController
{
    use ServiceContainer;
    use Request;

    public function render()
    {
        parent::render();
        $this->redirectAfterLogin();

        $user = $this->getUser();
        if ($user && $user->getFieldData('oxpassword')) {
            $this->setPaymentListsToView();
            return "modules/osc/unzer/account_saved_payments.tpl";
        }
        return  $this->_sThisLoginTemplate;
    }

    protected function setPaymentListsToView(): void
    {
        $this->_aViewData['unzerPaymentType'] = $this
            ->getServiceFromContainer(SavedPaymentViewService::class)
            ->getSavedPayments(
                $this->getUser(),
                SavedPaymentLoadService::SAVED_PAYMENT_ALL
            );
    }

    /**
     * @SuppressWarnings(PHPMD.StaticAccess)
     */
    public function deletePayment(): void
    {
        if (!\OxidEsales\Eshop\Core\Registry::getSession()->checkSessionChallenge()) {
            return;
        }

        $user = $this->getUser();
        if (!$user) {
            return;
        }

        $savedPaymentUserId = $this->getUnzerStringRequestParameter('savedPaymentUserId');
        $loadService = $this->getServiceFromContainer(SavedPaymentLoadService::class);
        // The saved payment id comes straight from the request and is a PayPal address or an IBAN,
        // so it identifies nobody: the lookup is restricted to the signed-in customer's own
        // transactions, otherwise anyone could delete a stranger's saved payment methods.
        $transactionsIds = $loadService->getSavedPaymentTransactionsByUserId(
            $savedPaymentUserId,
            (string)$user->getId()
        );

        if (count($transactionsIds) > 0) {
            $this->getServiceFromContainer(SavedPaymentSaveService::class)->unsetSavedPayments($transactionsIds);
        }
    }
}
