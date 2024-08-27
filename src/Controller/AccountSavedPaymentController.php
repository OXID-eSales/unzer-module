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
        $savedPaymentUserId = $this->getUnzerStringRequestParameter('savedPaymentUserId');
        $loadService = $this->getServiceFromContainer(SavedPaymentLoadService::class);
        $transactionsIds = $loadService->getSavedPaymentTransactionsByUserId(
            $savedPaymentUserId
        );

        if (count($transactionsIds) > 0) {
            $this->getServiceFromContainer(SavedPaymentSaveService::class)->unsetSavedPayments($transactionsIds);
        }
    }
}
